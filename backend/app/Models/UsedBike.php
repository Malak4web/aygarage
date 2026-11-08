<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsedBike extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'bike_model_code',
        'model_name',
        'family',
        'year',
        'km',
        'price',
        'installment_monthly',
        'inspection_score',
        'color',
        'owner_label',
        'license_until',
        'notes',
        'specs',
        'status',
        'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'km' => 'integer',
            'price' => 'integer',
            'installment_monthly' => 'integer',
            'inspection_score' => 'integer',
            'sold_count' => 'integer',
            'specs' => 'array',
        ];
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(UsedBikeInspection::class)->orderBy('sort');
    }

    public function images(): HasMany
    {
        return $this->hasMany(UsedBikeImage::class)->orderBy('sort');
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(UsedBikeServiceRecord::class)->orderBy('date', 'desc');
    }
}
