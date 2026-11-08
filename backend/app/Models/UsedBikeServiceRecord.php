<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsedBikeServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'used_bike_id',
        'date',
        'odometer',
        'work',
        'technician',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'odometer' => 'integer',
        ];
    }

    public function usedBike(): BelongsTo
    {
        return $this->belongsTo(UsedBike::class);
    }
}
