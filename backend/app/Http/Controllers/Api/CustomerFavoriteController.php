<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->with(['product.category', 'product.options', 'product.images'])
            ->get();

        $products = $favorites->pluck('product')->filter(fn($p) => $p && $p->is_active);

        return response()->json(ProductResource::collection($products));
    }

    public function store(Request $request, string $identifier): JsonResponse
    {
        $product = Product::where('is_active', true)
            ->where(function ($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? (int)$identifier : null);
            })
            ->first();

        if (!$product) {
            return response()->json(['message' => 'المنتج غير موجود أو غير متاح'], 404);
        }

        Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'تمت إضافة المنتج إلى المفضلة',
            'product' => new ProductResource($product),
        ], 201);
    }

    public function destroy(Request $request, string $identifier): JsonResponse
    {
        $product = Product::where(function ($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? (int)$identifier : null);
            })
            ->first();

        if ($product) {
            $request->user()->favorites()->where('product_id', $product->id)->delete();
        }

        return response()->json([
            'message' => 'تم حذف المنتج من المفضلة',
        ]);
    }
}
