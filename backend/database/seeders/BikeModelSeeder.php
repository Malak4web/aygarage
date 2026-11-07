<?php

namespace Database\Seeders;

use App\Models\BikeModel;
use Illuminate\Database\Seeder;

class BikeModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            ['code' => 'ZT125-U',  'name' => 'Zontes ZT125-U',  'sort' => 1],
            ['code' => 'ZT310-R',  'name' => 'Zontes ZT310-R',  'sort' => 2],
            ['code' => 'ZT310-X',  'name' => 'Zontes ZT310-X',  'sort' => 3],
            ['code' => 'ZT350-T',  'name' => 'Zontes ZT350-T',  'sort' => 4],
            ['code' => 'ZT350-GK', 'name' => 'Zontes ZT350-GK', 'sort' => 5],
            ['code' => 'ZT350-D',  'name' => 'Zontes ZT350-D',  'sort' => 6],
            ['code' => 'ZT703',    'name' => 'Zontes ZT703',    'sort' => 7],
        ];

        foreach ($models as $m) {
            BikeModel::updateOrCreate(
                ['code' => $m['code']],
                ['name' => $m['name'], 'sort' => $m['sort']]
            );
        }
    }
}
