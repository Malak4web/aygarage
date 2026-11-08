<?php

namespace Database\Seeders;

use App\Models\UsedBike;
use App\Models\UsedBikeInspection;
use App\Models\UsedBikeServiceRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsedBikeSeeder extends Seeder
{
    public function run(): void
    {
        $bikes = [
            [
                'slug' => 'zt350t-2023',
                'bike_model_code' => 'ZT350-T',
                'model_name' => 'Zontes ZT350-T',
                'family' => 'ZT350',
                'year' => 2023,
                'km' => 12400,
                'price' => 245000,
                'installment_monthly' => 10200,
                'inspection_score' => 92,
                'color' => '#9e2b25',
                'owner_label' => 'أول مالك',
                'license_until' => 'مارس 2027',
                'sold_count' => 4,
                'created_at' => Carbon::parse('2026-10-15 10:00:00'),
                'notes' => 'الموتوسيكل نضيف ومتصان عند الجراج من أول 6,000 كم. لمبة الإشارة الخلفية هتتغير مجاناً قبل التسليم. الكاوتش الخلفي 70% ومحتاج يتغير قبل 16,000 كم.',
                'specs' => [
                    'المحرك' => '348cc · سلندر واحد',
                    'القوة' => '38 حصان',
                    'ناقل الحركة' => '6 سرعات',
                    'الفرامل' => 'ABS مزدوج',
                    'الضمان' => '3 شهور على المحرك',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 94, 'note' => 'لمبة إشارة خلفي', 'is_warning' => true, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 75, 'note' => 'الخلفي 70%', 'is_warning' => true, 'sort' => 6],
                ],
                'service_records' => [
                    ['date' => '2026-10-12', 'odometer' => 12000, 'work' => 'تغيير زيت + فلتر', 'technician' => 'أبو يوسف'],
                    ['date' => '2026-07-05', 'odometer' => 9300,  'work' => 'تيل فرامل أمامي وخلفي', 'technician' => 'محمد'],
                    ['date' => '2026-03-20', 'odometer' => 6000,  'work' => 'صيانة شاملة', 'technician' => 'أبو يوسف'],
                ],
            ],
            [
                'slug' => 'zt125u-2024',
                'bike_model_code' => 'ZT125-U',
                'model_name' => 'Zontes ZT125-U',
                'family' => 'ZT125',
                'year' => 2024,
                'km' => 3200,
                'price' => 98000,
                'installment_monthly' => 4100,
                'inspection_score' => 97,
                'color' => '#2e2e2e',
                'owner_label' => 'أول مالك',
                'license_until' => 'أغسطس 2027',
                'sold_count' => 7,
                'created_at' => Carbon::parse('2026-10-20 12:00:00'),
                'notes' => 'بحالة الزيرو تماماً بدون أي خدوش، تم عمل صيانة الألف كم في التوكيل ومعه كافة المفاتيح الأصلية.',
                'specs' => [
                    'المحرك' => '125cc · تبريد ماء',
                    'القوة' => '14.8 حصان',
                    'ناقل الحركة' => '6 سرعات',
                    'الفرامل' => 'ABS أمامي وخلفي Bosch',
                    'الضمان' => 'ساري ضمان الوكيل',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 100, 'note' => 'سليم بحالة الزيرو', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 95, 'note' => 'بحالة المصنع', 'is_warning' => false, 'sort' => 6],
                ],
                'service_records' => [],
            ],
            [
                'slug' => 'zt350gk-2023',
                'bike_model_code' => 'ZT350-GK',
                'model_name' => 'Zontes ZT350-GK',
                'family' => 'ZT350',
                'year' => 2023,
                'km' => 7800,
                'price' => 232000,
                'installment_monthly' => 9600,
                'inspection_score' => 94,
                'color' => '#56634a',
                'owner_label' => 'أول مالك',
                'license_until' => 'يناير 2027',
                'sold_count' => 3,
                'created_at' => Carbon::parse('2026-10-05 09:30:00'),
                'notes' => 'موتوسيكل كافيه ريسر مميز جداً، تم تركيب حماية محرك جانبية وجراب موبايل أصلي.',
                'specs' => [
                    'المحرك' => '348cc · 38 حصان',
                    'القوة' => '38 حصان',
                    'ناقل الحركة' => '6 سرعات مع Slipper Clutch',
                    'الفرامل' => 'ديسك 320 مم كاليبر كبير',
                    'الضمان' => '3 شهور من الجراج',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 95, 'note' => 'تيل أمامي 80%', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 95, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 85, 'note' => 'حالة جيدة جداً', 'is_warning' => false, 'sort' => 6],
                ],
                'service_records' => [],
            ],
            [
                'slug' => 'zt310r-2022',
                'bike_model_code' => 'ZT310-R',
                'model_name' => 'Zontes ZT310-R',
                'family' => 'ZT310',
                'year' => 2022,
                'km' => 18900,
                'price' => 189000,
                'installment_monthly' => 7800,
                'inspection_score' => 86,
                'color' => '#d6d6d6',
                'owner_label' => 'أول مالك',
                'license_until' => 'مايو 2026',
                'sold_count' => 5,
                'created_at' => Carbon::parse('2026-09-10 14:15:00'),
                'notes' => 'حالة عامة جيدة جداً، به حكة بسيطة في الشكمان، تم عمل صيانة الـ 18 ألف بالكامل وتغيير الجنزير بـ DID ياباني جديد.',
                'specs' => [
                    'المحرك' => '312cc · سلندر واحد',
                    'القوة' => '35 حصان',
                    'ناقل الحركة' => '6 سرعات',
                    'الفرامل' => 'Bosch ABS',
                    'الضمان' => 'شهرين على المحرك',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 92, 'note' => 'سليم ولا توجد تسريبات', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 88, 'note' => 'تيل بحالة 65%', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 90, 'note' => 'سليم', 'is_warning' => false, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 85, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 70, 'note' => 'ملاحظة: الخلفي قرب يخلص', 'is_warning' => true, 'sort' => 6],
                ],
                'service_records' => [],
            ],
            [
                'slug' => 'zt310x-2021',
                'bike_model_code' => 'ZT310-X',
                'model_name' => 'Zontes ZT310-X',
                'family' => 'ZT310',
                'year' => 2021,
                'km' => 26000,
                'price' => 162000,
                'installment_monthly' => 6700,
                'inspection_score' => 81,
                'color' => '#2d4f80',
                'owner_label' => 'ثاني مالك',
                'license_until' => 'نوفمبر 2026',
                'sold_count' => 2,
                'created_at' => Carbon::parse('2026-08-25 11:45:00'),
                'notes' => 'موتوسيكل سفر مريح مع زجاج كهربائي متحرك، تم تغيير السير والزيوت ومستعد للسفر فوراً.',
                'specs' => [
                    'المحرك' => '312cc تورنج',
                    'القوة' => '35 حصان',
                    'ناقل الحركة' => '6 سرعات',
                    'الفرامل' => 'ABS مزدوج',
                    'الضمان' => 'شهر على المحرك',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 85, 'note' => 'سليم وصوته ناعم', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 80, 'note' => 'تيل جديد', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 85, 'note' => 'الزجاج الكهربائي يعمل بكفاءة', 'is_warning' => false, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 80, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 65, 'note' => 'ملاحظة: الكاوتش يحتاج تغيير قريباً', 'is_warning' => true, 'sort' => 6],
                ],
                'service_records' => [],
            ],
            [
                'slug' => 'zt350d-2024',
                'bike_model_code' => 'ZT350-D',
                'model_name' => 'Zontes ZT350-D',
                'family' => 'ZT350',
                'year' => 2024,
                'km' => 1900,
                'price' => 268000,
                'installment_monthly' => 11100,
                'inspection_score' => 98,
                'color' => '#8a8a8a',
                'owner_label' => 'أول مالك',
                'license_until' => 'أكتوبر 2027',
                'sold_count' => 8,
                'created_at' => Carbon::parse('2026-10-28 16:30:00'),
                'notes' => 'ماكسي سكوتر فخم وقوي جداً بحالة الوكالة تماماً، تم عمل صيانة التليين فقط.',
                'specs' => [
                    'المحرك' => '348cc سكوتر فائق القوة',
                    'القوة' => '36.8 حصان',
                    'ناقل الحركة' => 'CVT أوتوماتيك',
                    'الفرامل' => 'دبل ABS مع نظام تحكم بالجر TCS',
                    'الضمان' => 'ضمان الوكيل ساري',
                ],
                'status' => 'available',
                'inspections' => [
                    ['section' => 'المحرك', 'score' => 100, 'note' => 'بحالة الزيرو', 'is_warning' => false, 'sort' => 1],
                    ['section' => 'الفرامل وABS', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 2],
                    ['section' => 'الكهرباء', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 3],
                    ['section' => 'الشاسيه والرقم', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 4],
                    ['section' => 'العفشة', 'score' => 100, 'note' => 'سليم', 'is_warning' => false, 'sort' => 5],
                    ['section' => 'الكاوتش', 'score' => 98, 'note' => 'بحالة المصنع', 'is_warning' => false, 'sort' => 6],
                ],
                'service_records' => [],
            ],
        ];

        foreach ($bikes as $bData) {
            $inspections = $bData['inspections'];
            unset($bData['inspections']);

            $serviceRecords = $bData['service_records'];
            unset($bData['service_records']);

            $createdAt = $bData['created_at'];

            $bike = UsedBike::updateOrCreate(
                ['slug' => $bData['slug']],
                $bData
            );

            $bike->created_at = $createdAt;
            $bike->save();

            foreach ($inspections as $ins) {
                UsedBikeInspection::updateOrCreate(
                    [
                        'used_bike_id' => $bike->id,
                        'section' => $ins['section'],
                    ],
                    [
                        'score' => $ins['score'],
                        'note' => $ins['note'],
                        'is_warning' => $ins['is_warning'],
                        'sort' => $ins['sort'],
                    ]
                );
            }

            foreach ($serviceRecords as $rec) {
                UsedBikeServiceRecord::updateOrCreate(
                    [
                        'used_bike_id' => $bike->id,
                        'date' => $rec['date'],
                    ],
                    [
                        'odometer' => $rec['odometer'],
                        'work' => $rec['work'],
                        'technician' => $rec['technician'],
                    ]
                );
            }
        }
    }
}
