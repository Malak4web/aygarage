<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_subtotal',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_subtotal' => 'integer',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isValidFor(int $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($subtotal < $this->min_subtotal) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if (!$this->isValidFor($subtotal)) {
            return 0;
        }

        if ($this->type === 'percent') {
            return (int) round($subtotal * ($this->value / 100));
        }

        return min($this->value, $subtotal);
    }
}
