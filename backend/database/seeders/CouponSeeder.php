<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'ZONTES10'],
            [
                'type' => 'percent',
                'value' => 10,
                'min_subtotal' => 0,
                'is_active' => true,
                'expires_at' => null,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'GARAGE50'],
            [
                'type' => 'fixed',
                'value' => 50,
                'min_subtotal' => 200,
                'is_active' => true,
                'expires_at' => null,
            ]
        );
    }
}
