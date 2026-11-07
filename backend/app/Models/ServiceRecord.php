<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_bike_id',
        'booking_id',
        'date',
        'odometer',
        'work',
        'technician',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'odometer' => 'integer',
            'cost' => 'integer',
        ];
    }

    public function userBike(): BelongsTo
    {
        return $this->belongsTo(UserBike::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
