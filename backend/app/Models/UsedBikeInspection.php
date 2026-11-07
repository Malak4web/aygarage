<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsedBikeInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'used_bike_id',
        'section',
        'score',
        'note',
        'is_warning',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_warning' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function usedBike(): BelongsTo
    {
        return $this->belongsTo(UsedBike::class);
    }
}
