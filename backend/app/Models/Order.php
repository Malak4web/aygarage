<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'subtotal',
        'discount',
        'delivery_fee',
        'total',
        'coupon_code',
        'delivery_method',
        'payment_method',
        'payment_status',
        'name',
        'phone',
        'governorate',
        'area',
        'street',
        'delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'delivery_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            if ($order->isDirty('status')) {
                $oldStatus = $order->getOriginal('status');
                $newStatus = $order->status;

                // When transitioning to cancelled: return stock
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                        foreach ($order->items()->orderBy('product_id')->get() as $item) {
                            if ($item->product_id) {
                                Product::whereKey($item->product_id)->lockForUpdate()->first()?->increment('stock', $item->qty);
                            }
                        }
                    });
                }

                // When transitioning BACK from cancelled: check and decrement
                if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                        foreach ($order->items as $item) {
                            if ($item->product_id) {
                                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                                if ($product && $product->stock < $item->qty) {
                                    throw \Illuminate\Validation\ValidationException::withMessages([
                                        'status' => "لا يمكن إعادة تفعيل الطلب لأن كمية المنتج '{$product->name}' غير كافية في المخزون (المتبقي: {$product->stock}).",
                                    ]);
                                }
                            }
                        }

                        foreach ($order->items as $item) {
                            if ($item->product_id) {
                                Product::where('id', $item->product_id)->decrement('stock', $item->qty);
                            }
                        }
                    });
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
