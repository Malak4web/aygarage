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
        $product = Product::where('slug', 'motul-7100-10w40')->first();
        if (!$product) {
            return;
        }

        $reviews = [
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
        ];

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
