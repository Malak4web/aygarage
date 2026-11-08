<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusLabels = [
            'new' => 'جديد',
            'preparing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
            'ready_for_pickup' => 'جاهز للاستلام',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
        ];

        $deliveryDateFormatted = null;
        if ($this->delivery_method === 'pickup') {
            $deliveryDateFormatted = 'جاهز للاستلام من الجراج';
        } elseif ($this->delivery_date) {
            $deliveryDateFormatted = Carbon::parse($this->delivery_date)->locale('ar')->isoFormat('D MMMM YYYY');
        }

        return [
            'id' => (string) $this->number,
            'number' => (string) $this->number,
            'status' => $statusLabels[$this->status] ?? $this->status,
            'statusCode' => $this->status,
            'paymentStatus' => $this->payment_status,
            'date' => $this->created_at ? Carbon::parse($this->created_at)->locale('ar')->isoFormat('D MMMM YYYY') : '',
            'rawDate' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d') : '',
            'subtotal' => $this->subtotal,
            'subtotalNum' => $this->subtotal,
            'discount' => $this->discount,
            'discountNum' => $this->discount,
            'deliveryFee' => $this->delivery_fee,
            'deliveryFeeNum' => $this->delivery_fee,
            'total' => number_format($this->total) . ' ج.م',
            'totalNum' => $this->total,
            'couponCode' => $this->coupon_code,
            'deliveryMethod' => $this->delivery_method,
            'paymentMethod' => $this->payment_method,
            'deliveryDate' => $deliveryDateFormatted,
            'rawDeliveryDate' => $this->delivery_date ? Carbon::parse($this->delivery_date)->format('Y-m-d') : null,
            'customer' => [
                'name' => $this->name,
                'phone' => $this->phone,
                'governorate' => $this->governorate,
                'area' => $this->area,
                'address' => $this->street,
                'street' => $this->street,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
