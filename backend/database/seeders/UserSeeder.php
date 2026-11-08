<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\UserBike;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $adminPassword = env('SEED_ADMIN_PASSWORD');
        if (empty($adminPassword)) {
            throw new \RuntimeException('SEED_ADMIN_PASSWORD is missing in .env. Please set it before seeding.');
        }

        $admin = User::firstOrNew(['phone' => '01000000001']);
        $admin->forceFill([
            'name' => 'أبو يوسف (المدير)',
            'email' => 'admin@aygarage.com',
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
        ])->save();

        // 2. Demo Customer Karim
        $demoPassword = env('SEED_DEMO_PASSWORD');
        if (empty($demoPassword)) {
            throw new \RuntimeException('SEED_DEMO_PASSWORD is missing in .env. Please set it before seeding.');
        }

        $karim = User::firstOrNew(['phone' => '01012345678']);
        $karim->forceFill([
            'name' => 'كريم محمود',
            'email' => 'karim@aygarage.com',
            'password' => Hash::make($demoPassword),
            'role' => 'customer',
        ])->save();

        // Karim's Address
        Address::updateOrCreate(
            [
                'user_id' => $karim->id,
                'street' => '12 شارع عباس العقاد، الدور 3، شقة 7',
            ],
            [
                'governorate' => 'القاهرة',
                'area' => 'مدينة نصر',
                'is_default' => true,
            ]
        );

        // Karim's Bike
        $bike = UserBike::updateOrCreate(
            [
                'user_id' => $karim->id,
                'bike_model_code' => 'ZT350-T',
            ],
            [
                'model_name' => 'Zontes ZT350-T',
                'year' => 2023,
                'plate' => 'س ط ع 1234',
                'odometer' => 12400,
                'next_service_km' => 15000,
                'license_until' => 'مارس 2027',
                'color' => '#9e2b25',
            ]
        );

        // Karim's Service Records (maintenance history)
        $records = [
            [
                'date' => '2026-10-12',
                'odometer' => 12000,
                'work' => 'تغيير زيت + فلتر',
                'technician' => 'أبو يوسف',
                'cost' => 1770,
            ],
            [
                'date' => '2026-07-05',
                'odometer' => 9300,
                'work' => 'تيل فرامل أمامي وخلفي',
                'technician' => 'محمد',
                'cost' => 1240,
            ],
            [
                'date' => '2026-03-20',
                'odometer' => 6000,
                'work' => 'صيانة شاملة',
                'technician' => 'أبو يوسف',
                'cost' => 2450,
            ],
            [
                'date' => '2026-01-02',
                'odometer' => 4100,
                'work' => 'فحص كهرباء وبطارية',
                'technician' => 'محمد',
                'cost' => 350,
            ],
        ];

        foreach ($records as $rec) {
            ServiceRecord::updateOrCreate(
                [
                    'user_bike_id' => $bike->id,
                    'date' => $rec['date'],
                ],
                [
                    'odometer' => $rec['odometer'],
                    'work' => $rec['work'],
                    'technician' => $rec['technician'],
                    'cost' => $rec['cost'],
                ]
            );
        }

        // Demo Booking BK-1042
        $oilService = Service::where('code', 's1')->first();
        if ($oilService) {
            Booking::updateOrCreate(
                ['number' => 'BK-1042'],
                [
                    'user_id' => $karim->id,
                    'user_bike_id' => $bike->id,
                    'service_id' => $oilService->id,
                    'date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                    'time' => '11:00',
                    'price_snapshot' => $oilService->price,
                    'status' => 'confirmed',
                    'notes' => 'صيانة دورية للـ 15 ألف',
                ]
            );
        }

        // Demo Orders: 1042 and 0987
        $p1 = Product::where('slug', 'motul-7100-10w40')->first();
        $p2 = Product::where('slug', 'zontes-oil-filter-zt350')->first();
        $p4 = Product::where('slug', 'ls2-rapid-ii-helmet')->first();

        // Order 1042
        $order1042 = Order::updateOrCreate(
            ['number' => '1042'],
            [
                'user_id' => $karim->id,
                'status' => 'delivered',
                'subtotal' => 3220,
                'discount' => 0,
                'delivery_fee' => 0,
                'total' => 3220,
                'delivery_method' => 'home',
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'name' => 'كريم محمود',
                'phone' => '01012345678',
                'governorate' => 'القاهرة',
                'area' => 'مدينة نصر',
                'street' => '12 شارع عباس العقاد، الدور 3، شقة 7',
                'delivery_date' => '2026-11-06',
                'created_at' => Carbon::parse('2026-11-03 14:30:00'),
                'updated_at' => Carbon::parse('2026-11-06 12:00:00'),
            ]
        );

        if ($p1 && $p2) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $order1042->id,
                    'product_id' => $p1->id,
                ],
                [
                    'option_code' => '1l',
                    'name_snapshot' => $p1->name,
                    'unit_price' => 1450,
                    'qty' => 2,
                    'line_total' => 2900,
                ]
            );

            OrderItem::updateOrCreate(
                [
                    'order_id' => $order1042->id,
                    'product_id' => $p2->id,
                ],
                [
                    'option_code' => null,
                    'name_snapshot' => $p2->name,
                    'unit_price' => 320,
                    'qty' => 1,
                    'line_total' => 320,
                ]
            );
        }

        // Order 0987
        $order0987 = Order::updateOrCreate(
            ['number' => '0987'],
            [
                'user_id' => $karim->id,
                'status' => 'delivered',
                'subtotal' => 4200,
                'discount' => 0,
                'delivery_fee' => 0,
                'total' => 4200,
                'delivery_method' => 'pickup',
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'name' => 'كريم محمود',
                'phone' => '01012345678',
                'governorate' => null,
                'area' => null,
                'street' => null,
                'delivery_date' => '2026-09-15',
                'created_at' => Carbon::parse('2026-09-14 10:15:00'),
                'updated_at' => Carbon::parse('2026-09-15 16:00:00'),
            ]
        );

        if ($p4) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $order0987->id,
                    'product_id' => $p4->id,
                ],
                [
                    'option_code' => null,
                    'name_snapshot' => $p4->name,
                    'unit_price' => 4200,
                    'qty' => 1,
                    'line_total' => 4200,
                ]
            );
        }
    }
}
