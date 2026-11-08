<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dateStr = $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null;

        return [
            'id' => $this->id,
            'userBikeId' => $this->user_bike_id,
            'bookingId' => $this->booking_id,
            'date' => $dateStr,
            'odo' => number_format((int) $this->odometer) . ' كم',
            'odometer' => (int) $this->odometer,
            'work' => $this->work,
            'service' => $this->work,
            'tech' => $this->technician,
            'technician' => $this->technician,
            'cost' => number_format((int) $this->cost),
            'costNum' => (int) $this->cost,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
