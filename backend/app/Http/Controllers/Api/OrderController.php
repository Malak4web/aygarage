<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->with(['items.product.images'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, string $number): JsonResponse|OrderResource
    {
        $order = $request->user()->orders()
            ->with(['items.product.images'])
            ->where(function ($q) use ($number) {
                $q->where('number', $number)
                    ->orWhere('id', $number);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'message' => "الطلب #{$number} غير موجود",
            ], 404);
        }

        return new OrderResource($order);
    }

    public function store(OrderCreateRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request) {
            $itemsInput = $request->input('items', []);

            // Collect all product identifiers (IDs, prefixed IDs, or slugs)
            $productKeys = [];
            foreach ($itemsInput as $item) {
                $candidates = [
                    $item['productId'] ?? null,
                    $item['product_id'] ?? null,
                    $item['slug'] ?? null,
                ];
                foreach ($candidates as $k) {
                    if ($k !== null && $k !== '') {
                        $productKeys[] = (string) $k;
                        if (preg_match('/^p(\d+)$/', (string) $k, $m)) {
                            $productKeys[] = $m[1];
                        }
                    }
                }
            }
            $productKeys = array_values(array_unique($productKeys));

            // Lock products for update and eager-load options in one query
            $products = Product::with('options')
                ->whereIn('id', $productKeys)
                ->orWhereIn('slug', $productKeys)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $requiredStockPerProduct = [];
            $itemResolvedProducts = [];

            // Match each requested item to a locked product
            foreach ($itemsInput as $idx => $item) {
                $product = $products->first(function ($p) use ($item) {
                    $candidates = [
                        $item['productId'] ?? null,
                        $item['product_id'] ?? null,
                        $item['slug'] ?? null,
                    ];
                    foreach ($candidates as $k) {
                        if ($k === null || $k === '') continue;
                        $strK = (string) $k;
                        $numK = preg_match('/^p(\d+)$/', $strK, $m) ? $m[1] : $strK;
                        if ((string) $p->id === $strK || (string) $p->id === $numK || $p->slug === $strK) {
                            return true;
                        }
                    }
                    return false;
                });

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$idx}" => 'أحد المنتجات المطلوبة غير متوفر حالياً',
                    ]);
                }

                $qty = max(1, (int) ($item['qty'] ?? 1));
                $itemResolvedProducts[$idx] = $product;
                $requiredStockPerProduct[$product->id] = ($requiredStockPerProduct[$product->id] ?? 0) + $qty;
            }

            // Verify stock availability: send one clean message per item line
            $stockErrors = [];
            foreach ($requiredStockPerProduct as $prodId => $totalReqQty) {
                $product = $products->firstWhere('id', $prodId);
                if ($product && $product->stock < $totalReqQty) {
                    $stockMsg = "الكمية المطلوبة من {$product->name} غير متوفرة في المخزن (المتاح: {$product->stock})";
                    foreach ($itemsInput as $idx => $item) {
                        $p = $itemResolvedProducts[$idx] ?? null;
                        if ($p && $p->id === $prodId) {
                            $stockErrors["items.{$idx}.qty"] = $stockMsg;
                        }
                    }
                }
            }

            if (!empty($stockErrors)) {
                throw ValidationException::withMessages($stockErrors);
            }

            // Validate options & calculate item lines and subtotal from DB prices
            $orderItemsData = [];
            $subtotal = 0;

            foreach ($itemsInput as $idx => $item) {
                $product = $itemResolvedProducts[$idx];
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $optionCode = $item['optionCode'] ?? ($item['option_code'] ?? null);

                $unitPrice = $product->price;
                $nameSnapshot = $product->name;

                $hasOptions = $product->options && $product->options->isNotEmpty();
                if ($hasOptions) {
                    if (empty($optionCode)) {
                        throw ValidationException::withMessages([
                            "items.{$idx}.optionCode" => "يرجى اختيار أحد الخيارات المتاحة للمنتج {$product->name}",
                        ]);
                    }

                    $option = $product->options->firstWhere('code', $optionCode);
                    if (!$option) {
                        throw ValidationException::withMessages([
                            "items.{$idx}.optionCode" => 'الاختيار ده مش متاح',
                        ]);
                    }

                    $unitPrice = $option->price;
                    $nameSnapshot .= " ({$option->label})";
                } else {
                    if (!empty($optionCode)) {
                        throw ValidationException::withMessages([
                            "items.{$idx}.optionCode" => 'الاختيار ده مش متاح',
                        ]);
                    }
                }

                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'option_code' => $optionCode,
                    'name_snapshot' => $nameSnapshot,
                    'unit_price' => $unitPrice,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            // Decrement stock for each product
            foreach ($requiredStockPerProduct as $prodId => $totalReqQty) {
                $product = $products->firstWhere('id', $prodId);
                $product->decrement('stock', $totalReqQty);
            }

            // Coupon calculation
            $couponCode = $request->input('coupon_code');
            $discount = 0;
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if (!$coupon || !$coupon->is_active || ($coupon->expires_at && $coupon->expires_at->isPast())) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'كود الخصم غير صحيح أو منتهي الصلاحية',
                    ]);
                }
                if ($coupon->min_subtotal > 0 && $subtotal < $coupon->min_subtotal) {
                    throw ValidationException::withMessages([
                        'coupon_code' => "الكوبون محتاج طلب بـ {$coupon->min_subtotal} ج.م على الأقل",
                    ]);
                }
                $discount = $coupon->calculateDiscount($subtotal);
            }

            // Delivery fee calculation using Setting::get()
            $deliveryMethod = $request->input('delivery_method');
            $deliveryFee = 0;
            if ($deliveryMethod === 'home') {
                $deliveryFeeSetting = (int) Setting::get('delivery_fee', 60);
                $freeThresholdSetting = (int) Setting::get('free_delivery_threshold', 3000);
                $deliveryFee = ($subtotal >= $freeThresholdSetting) ? 0 : $deliveryFeeSetting;
            }

            // Total calculation
            $total = max(0, $subtotal - $discount + $deliveryFee);

            // Generate sequential unique order number
            $maxNum = Order::lockForUpdate()->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')->value('max_num');
            $nextNumber = (string) max(1043, ((int) $maxNum) + 1);

            // Delivery date calculation from settings
            $deliveryDays = (int) Setting::get('delivery_days_count', 3);
            $deliveryDate = now()->addDays($deliveryDays)->format('Y-m-d');

            $user = $request->user();

            $order = Order::create([
                'number' => $nextNumber,
                'user_id' => $user?->id,
                'status' => 'new',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'coupon_code' => $couponCode,
                'delivery_method' => $deliveryMethod,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => 'pending',
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'governorate' => $deliveryMethod === 'home' ? $request->input('governorate') : null,
                'area' => $deliveryMethod === 'home' ? $request->input('area') : null,
                'street' => $deliveryMethod === 'home' ? $request->input('street') : null,
                'delivery_date' => $deliveryDate,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        $order->load(['items.product.images']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
