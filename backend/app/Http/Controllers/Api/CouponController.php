<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponCheckRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function check(CouponCheckRequest $request): JsonResponse
    {
        $code = $request->validated('code');
        $subtotal = (int) $request->validated('subtotal');

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->is_active || ($coupon->expires_at && $coupon->expires_at->isPast())) {
            return response()->json([
                'message' => 'كود الخصم غير صحيح أو منتهي الصلاحية',
            ], 422);
        }

        if ($coupon->min_subtotal > 0 && $subtotal < $coupon->min_subtotal) {
            return response()->json([
                'message' => "الحد الأدنى لتفعيل هذا الكود هو {$coupon->min_subtotal} ج.م",
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
            'message' => 'تم تطبيق كود الخصم بنجاح',
        ]);
    }
}
