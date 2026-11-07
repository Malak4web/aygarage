<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'brand',
        'sku',
        'category_id',
        'type',
        'color',
        'price',
        'old_price',
        'stock',
        'rating',
        'review_count',
        'sold_count',
        'badge',
        'is_best_seller',
        'is_weekly_offer',
        'viscosity',
        'oil_type',
        'fit_note',
        'description',
        'specs',
        'fits',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'fits' => 'array',
            'price' => 'integer',
            'old_price' => 'integer',
            'stock' => 'integer',
            'rating' => 'float',
            'review_count' => 'integer',
            'sold_count' => 'integer',
            'is_best_seller' => 'boolean',
            'is_weekly_offer' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
