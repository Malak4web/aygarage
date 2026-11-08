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
        'last_visit_at',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'odometer' => 'integer',
            'next_service_km' => 'integer',
            'last_visit_at' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bikeModel(): BelongsTo
    {
        return $this->belongsTo(BikeModel::class, 'bike_model_code', 'code');
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
