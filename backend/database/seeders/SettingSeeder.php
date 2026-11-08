<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'store_name' => 'جراج أبو يوسف',
            'store_name_en' => 'Abu Yousef Garage',
            'tagline' => 'ZONTES SPECIALIST',
            'phone' => '010 0000 0000',
            'whatsapp' => '201000000000',
            'announcement' => 'توصيل لكل المحافظات · استلام مجاني من الجراج · كلّمنا: 010 0000 0000',
            'delivery_fee' => 60,
            'free_delivery_threshold' => 3000,
            'delivery_days_count' => 3,
            'delivery_estimate_days' => '2-4 أيام عمل',
            'return_days' => 14,
            'return_policy_note' => 'لو العبوة ما اتفتحتش أو القطعة ما ركبتش',
            'total_inspected_bikes' => 22,
            'default_installment_monthly' => 10200,
            'hero_kit_price' => 5350,
            'hero_kit_discount' => '10%',
            'models' => [
                'ZT125-U',
                'ZT310-R',
                'ZT310-X',
                'ZT350-T',
                'ZT350-GK',
                'ZT350-D',
                'ZT703',
            ],
            'years' => [2026, 2025, 2024, 2023, 2022, 2021, 2020],
            'payment_methods' => [
                ['id' => 'cod', 'title' => 'كاش عند الاستلام', 'desc' => 'ادفع للمندوب لما الطلب يوصل'],
                ['id' => 'pickup', 'title' => 'الدفع عند الاستلام في الجراج', 'desc' => 'استلم طلبك وادفع كاش في مقر الجراج'],
            ],
            'governorates' => [
                [
                    'name' => 'القاهرة',
                    'areas' => ['مدينة نصر', 'مصر الجديدة', 'التجمع الخامس', 'المعادي', 'شبرا', 'الدقي', 'المهندسين'],
                ],
                [
                    'name' => 'الجيزة',
                    'areas' => ['الدقي', 'المهندسين', 'الهرم', 'فيصل', 'الشيخ زايد', '6 أكتوبر'],
                ],
                [
                    'name' => 'الإسكندرية',
                    'areas' => ['سموحة', 'سيدي جابر', 'لوران', 'ميامي', 'ستانلي', 'محرم بك'],
                ],
                [
                    'name' => 'الشرقية',
                    'areas' => ['الزقازيق', 'العاشر من رمضان', 'بلبيس', 'منيا القمح'],
                ],
            ],
            'service_features' => [
                ['title' => 'تغيير زيت وفلتر', 'price' => '1,770 ج.م'],
                ['title' => 'صيانة شاملة', 'price' => '2,450 ج.م'],
                ['title' => 'فحص قبل الشراء', 'price' => '600 ج.م'],
                ['title' => 'ضبط بلوف', 'price' => '900 ج.م'],
            ],
            'trust_items' => [
                ['icon' => 'truck', 'title' => 'توصيل لكل المحافظات', 'desc' => 'خلال 2-4 أيام عمل'],
                ['icon' => 'shield', 'title' => 'قطع أصلية', 'desc' => 'من الوكيل أو الشركة المصنعة'],
                ['icon' => 'cash', 'title' => 'الدفع عند الاستلام', 'desc' => 'كاش للمندوب أو في الجراج'],
                ['icon' => 'ret', 'title' => 'استرجاع خلال 14 يوم', 'desc' => 'لو القطعة ما ركبتش'],
            ],
            'pickup_times' => 'جاهز النهارده بعد 3 العصر',
            'booking_slots' => [
                ['time' => '11:00', 'label' => '11:00 ص', 'off' => false],
                ['time' => '12:00', 'label' => '12:00 م', 'off' => true],
                ['time' => '13:00', 'label' => '1:00 م',  'off' => false],
                ['time' => '14:00', 'label' => '2:00 م',  'off' => false],
                ['time' => '15:00', 'label' => '3:00 م',  'off' => false],
                ['time' => '16:00', 'label' => '4:00 م',  'off' => false],
                ['time' => '17:00', 'label' => '5:00 م',  'off' => true],
                ['time' => '18:00', 'label' => '6:00 م',  'off' => false],
                ['time' => '19:00', 'label' => '7:00 م',  'off' => false],
                ['time' => '20:00', 'label' => '8:00 م',  'off' => false],
            ],
            'booking_capacity_per_slot' => 2,
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $val]
            );
        }
    }
}
