<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_check_valid_percent(): void
    {
        Coupon::create([
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
            'min_subtotal' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/coupons/check', [
            'code' => 'test10',
            'subtotal' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'code' => 'TEST10',
                'type' => 'percent',
                'value' => 10,
                'discount' => 100,
            ]);
    }

    public function test_coupon_check_inactive_or_expired_rejected(): void
    {
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 50,
            'min_subtotal' => 0,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->postJson('/api/coupons/check', [
            'code' => 'EXPIRED',
            'subtotal' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'كود الخصم غير صحيح أو منتهي الصلاحية']);
    }

    public function test_coupon_check_min_subtotal_enforced(): void
    {
        Coupon::create([
            'code' => 'MIN1000',
            'type' => 'fixed',
            'value' => 100,
            'min_subtotal' => 1000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/coupons/check', [
            'code' => 'MIN1000',
            'subtotal' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'الحد الأدنى لتفعيل هذا الكود هو 1000 ج.م']);
    }
}
