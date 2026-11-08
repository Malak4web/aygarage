<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewsByProduct = [
            'motul-7100-10w40' => [
                [
                    'author_name' => 'أحمد إبراهيم',
                    'rating' => 5,
                    'body' => 'زيت ممتاز جداً صوته ناعم مع محرك الـ 350 ونقلات الجير بقت سلسة جداً بدون أي تكة.',
                    'created_at' => Carbon::now()->subWeek(),
                ],
                [
                    'author_name' => 'محمد فتحي',
                    'rating' => 5,
                    'body' => 'التوصيل كان سريع جداً واستلمت القطع أصلية بالباركود من جراج أبو يوسف.',
                    'created_at' => Carbon::now()->subWeeks(2),
                ],
                [
                    'author_name' => 'عمر ياسر',
                    'rating' => 4,
                    'body' => 'منتج أصلي وممتاز، والتعامل مع فريق الدعم محترم وسريع في الرد على استفسارات التوافق.',
                    'created_at' => Carbon::now()->subMonth(),
                ],
            ],
            'zontes-oil-filter-zt350' => [
                [
                    'author_name' => 'طارق نبيل',
                    'rating' => 5,
                    'body' => 'فلتر أصلي بختم زونتس، بيفرق جداً في نقاء الزيت وعزم المكنة.',
                    'created_at' => Carbon::now()->subDays(10),
                ],
                [
                    'author_name' => 'محمود عادل',
                    'rating' => 5,
                    'body' => 'ركب تماماً على ZT350-T وبدون أي تسريب مع الجوانات الأصلية.',
                    'created_at' => Carbon::now()->subWeeks(3),
                ],
            ],
            'brembo-front-brake-pads' => [
                [
                    'author_name' => 'حازم شريف',
                    'rating' => 5,
                    'body' => 'قوة فرملة جبارة واستجابة فورية بدون صوت صفير أو تآكل للطنبورة.',
                    'created_at' => Carbon::now()->subDays(5),
                ],
                [
                    'author_name' => 'يوسف عبد الرحمن',
                    'rating' => 4,
                    'body' => 'تيل محترم وسيراميك نظيف جداً، فرملة الـ ABS بقت متزنة أكتر.',
                    'created_at' => Carbon::now()->subWeeks(4),
                ],
            ],
            'ls2-rapid-ii-helmet' => [
                [
                    'author_name' => 'سيف الدين',
                    'rating' => 5,
                    'body' => 'خوذة مريحة وعزل الهواء فيها ممتاز على السرعات العالية، وزنها خفيف.',
                    'created_at' => Carbon::now()->subDays(12),
                ],
                [
                    'author_name' => 'كريم علام',
                    'rating' => 4,
                    'body' => 'الفيزر مقاوم للخدش وجودة الخامات ممتازة بالنسبة للسعر.',
                    'created_at' => Carbon::now()->subMonth(),
                ],
            ],
            'did-520-vx3-chain' => [
                [
                    'author_name' => 'إسلام جاد',
                    'rating' => 5,
                    'body' => 'جنزير ياباني أصلي بحلقات X، مفيش أي رخي أو صوت بعد 3000 كم سفر.',
                    'created_at' => Carbon::now()->subDays(8),
                ],
                [
                    'author_name' => 'عمرو كمال',
                    'rating' => 5,
                    'body' => 'أفضل جنزير جربته للـ 310، ناعم جداً ونقل القوة للعجلة الخلفية سلس.',
                    'created_at' => Carbon::now()->subWeeks(2),
                ],
            ],
        ];

        foreach ($reviewsByProduct as $productSlug => $reviews) {
            $product = Product::where('slug', $productSlug)->first();
            if (!$product) {
                continue;
            }

            foreach ($reviews as $rev) {
                Review::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'author_name' => $rev['author_name'],
                    ],
                    [
                        'rating' => $rev['rating'],
                        'body' => $rev['body'],
                        'is_approved' => true,
                        'created_at' => $rev['created_at'],
                        'updated_at' => $rev['created_at'],
                    ]
                );
            }
        }
    }
}
