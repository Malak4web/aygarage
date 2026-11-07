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
    }
}
