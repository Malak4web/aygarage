<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'oils',         'name' => 'زيوت',             'icon' => 'oil',     'sort' => 1],
            ['slug' => 'filters',      'name' => 'فلاتر',            'icon' => 'filter',  'sort' => 2],
            ['slug' => 'brakes',       'name' => 'فرامل',            'icon' => 'brake',   'sort' => 3],
            ['slug' => 'batteries',    'name' => 'كهرباء وبطاريات',   'icon' => 'battery', 'sort' => 4],
            ['slug' => 'tires',        'name' => 'إطارات',           'icon' => 'tyre',    'sort' => 5],
            ['slug' => 'transmission', 'name' => 'نقل حركة',         'icon' => 'chain',   'sort' => 6],
            ['slug' => 'helmets',      'name' => 'خوذ وملابس',       'icon' => 'helmet',  'sort' => 7],
            ['slug' => 'accessories',  'name' => 'إكسسوارات',        'icon' => 'glove',   'sort' => 8],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'sort' => $cat['sort'],
                ]
            );
        }
    }
}
