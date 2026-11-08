<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'user_bike_id',
        'custom_model',
        'odometer',
        'service_id',
        'date',
        'time',
        'price_snapshot',
        'status',
        'phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'odometer' => 'integer',
            'price_snapshot' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->number)) {
                $booking->number = 'BK-TEMP-' . (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::created(function (Booking $booking) {
            if (str_starts_with($booking->number, 'BK-TEMP-')) {
                $booking->updateQuietly([
                    'number' => 'BK-' . (1042 + $booking->id),
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userBike(): BelongsTo
    {
        return $this->belongsTo(UserBike::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceRecord(): HasOne
    {
        return $this->hasOne(ServiceRecord::class);
    }
}
