<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $raw = Setting::pluck('value', 'key')->all();

        $deliveryDaysCount = (int) ($raw['delivery_days_count'] ?? 3);
        $deliveryEstimateDays = $raw['delivery_estimate_days'] ?? '2-4 أيام عمل';
        $returnDays = (int) ($raw['return_days'] ?? 14);

        $targetDate = Carbon::now()->addDays($deliveryDaysCount)->locale('ar');
        $dayName = $targetDate->isoFormat('dddd');
        $dayNum = $targetDate->isoFormat('D');
        $monthName = $targetDate->isoFormat('MMMM');
        $deliveryEstimate = "يوصلك {$dayName} {$dayNum} {$monthName}";

        $heroKitPrice = (int) ($raw['hero_kit_price'] ?? 5350);

        // Item 16: Dynamically format trust items from returnDays and deliveryEstimateDays
        $trustItems = $raw['trust_items'] ?? [];
        if (is_array($trustItems)) {
            foreach ($trustItems as &$item) {
                if (isset($item['icon'])) {
                    if ($item['icon'] === 'truck') {
                        $item['desc'] = "خلال {$deliveryEstimateDays}";
                    } elseif ($item['icon'] === 'ret') {
                        $item['title'] = "استرجاع خلال {$returnDays} يوم";
                    } elseif ($item['icon'] === 'cash') {
                        $item['title'] = 'الدفع عند الاستلام';
                        $item['desc'] = 'كاش للمندوب أو في الجراج';
                    }
                }
            }
            unset($item);
        }

        $data = [
            'name' => $raw['store_name'] ?? 'جراج أبو يوسف',
            'nameEn' => $raw['store_name_en'] ?? 'Abu Yousef Garage',
            'tagline' => $raw['tagline'] ?? 'ZONTES SPECIALIST',
            'phone' => $raw['phone'] ?? '010 0000 0000',
            'whatsapp' => $raw['whatsapp'] ?? '201000000000',
            'announcement' => $raw['announcement'] ?? '',
            'deliveryFee' => (int) ($raw['delivery_fee'] ?? 60),
            'freeDeliveryThreshold' => (int) ($raw['free_delivery_threshold'] ?? 3000),
            'deliveryDaysCount' => $deliveryDaysCount,
            'deliveryEstimateDays' => $deliveryEstimateDays,
            'deliveryEstimate' => $deliveryEstimate,
            'returnDays' => $returnDays,
            'returnPolicyNote' => $raw['return_policy_note'] ?? '',
            'totalInspectedBikes' => (int) ($raw['total_inspected_bikes'] ?? 22),
            'defaultInstallmentMonthly' => (int) ($raw['default_installment_monthly'] ?? 10200),
            'heroKitPrice' => $heroKitPrice,
            'heroKitPriceFormatted' => number_format($heroKitPrice) . ' ج.م',
            'heroKitDiscount' => $raw['hero_kit_discount'] ?? '10%',
            'models' => $raw['models'] ?? [],
            'years' => $raw['years'] ?? [],
            'paymentMethods' => $raw['payment_methods'] ?? [],
            'governorates' => $raw['governorates'] ?? [],
            'serviceFeatures' => $raw['service_features'] ?? [],
            'trustItems' => $trustItems,
            'pickupTimes' => $raw['pickup_times'] ?? 'جاهز النهارده بعد 3 العصر',
            'bookingSlots' => $raw['booking_slots'] ?? [],
            'bookingCapacityPerSlot' => (int) ($raw['booking_capacity_per_slot'] ?? 2),
        ];

        return response()->json($data);
    }
}
