<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bike_model_code',
        'model_name',
        'year',
        'plate',
        'odometer',
        'next_service_km',
        'license_until',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'odometer' => 'integer',
            'next_service_km' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class)->orderBy('date', 'desc');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function latestServiceRecord()
    {
        return $this->hasOne(ServiceRecord::class)->latestOfMany('date');
    }
}
