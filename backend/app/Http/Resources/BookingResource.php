<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $timeArabic = null;
        if ($this->time) {
            $parsed = Carbon::createFromFormat('H:i:s', strlen($this->time) === 5 ? $this->time . ':00' : $this->time)
                ?: Carbon::parse($this->time);
            $hour = (int) $parsed->format('g');
            $minute = $parsed->format('i');
            $ampm = $parsed->format('A') === 'AM' ? 'ص' : 'م';
            $timeArabic = "{$hour}:{$minute} {$ampm}";
        }

        return [
            'id' => $this->id,
            'number' => $this->number,
            'userId' => $this->user_id,
            'userBikeId' => $this->user_bike_id,
            'customModel' => $this->custom_model,
            'odometer' => $this->odometer,
            'serviceId' => $this->service_id,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'date' => $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null,
            'time' => $this->time ? substr($this->time, 0, 5) : null,
            'timeArabic' => $timeArabic,
            'priceSnapshot' => $this->price_snapshot,
            'status' => $this->status,
            'notes' => $this->notes,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
