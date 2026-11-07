<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsedBikeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'used_bike_id',
        'path',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function usedBike(): BelongsTo
    {
        return $this->belongsTo(UsedBike::class);
    }
}
