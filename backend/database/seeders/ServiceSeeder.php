<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 's1',
                'name' => 'تغيير زيت وفلتر',
                'duration_label' => '45 دقيقة',
                'price' => 1770,
                'price_label' => '1,770 ج.م',
                'is_active' => true,
                'sort' => 1,
            ],
            [
                'code' => 's2',
                'name' => 'صيانة شاملة',
                'duration_label' => '3 ساعات',
                'price' => 2450,
                'price_label' => '2,450 ج.م',
                'is_active' => true,
                'sort' => 2,
            ],
            [
                'code' => 's3',
                'name' => 'فحص قبل الشراء',
                'duration_label' => '90 دقيقة',
                'price' => 600,
                'price_label' => '600 ج.م',
                'is_active' => true,
                'sort' => 3,
            ],
            [
                'code' => 's4',
                'name' => 'فرامل',
                'duration_label' => 'ساعة',
                'price' => null,
                'price_label' => 'حسب القطع',
                'is_active' => true,
                'sort' => 4,
            ],
            [
                'code' => 's5',
                'name' => 'كهرباء',
                'duration_label' => 'حسب الفحص',
                'price' => null,
                'price_label' => 'حسب العطل',
                'is_active' => true,
                'sort' => 5,
            ],
            [
                'code' => 's6',
                'name' => 'ضبط بلوف',
                'duration_label' => 'ساعتين',
                'price' => 900,
                'price_label' => '900 ج.م',
                'is_active' => true,
                'sort' => 6,
            ],
        ];

        foreach ($services as $srv) {
            Service::updateOrCreate(
                ['code' => $srv['code']],
                $srv
            );
        }
    }
}
