<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'bike_model_code',
        'year',
        'km',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'km' => 'integer',
        ];
    }

    public function bikeModel(): BelongsTo
    {
        return $this->belongsTo(BikeModel::class, 'bike_model_code', 'code');
    }
}
