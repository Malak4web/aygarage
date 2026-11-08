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
            $parsed = Carbon::createFromFormat('H:i', substr($this->time, 0, 5));
            if ($parsed) {
                $hour = (int) $parsed->format('g');
                $minute = $parsed->format('i');
                $ampm = $parsed->format('A') === 'AM' ? 'ص' : 'م';
                $timeArabic = "{$hour}:{$minute} {$ampm}";
            }
        }

        $bikeTitle = $this->userBike
            ? ($this->userBike->model_name ?: ('Zontes ' . $this->userBike->bike_model_code)) . ' · ' . $this->userBike->year
            : ($this->custom_model ?: 'موتوسيكل');

        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'done' => 'تم',
            'cancelled' => 'ملغي',
        ];
        $statusLabel = $statusLabels[$this->status] ?? $this->status;

        $costFormatted = null;
        if ($this->price_snapshot !== null) {
            $costFormatted = $this->price_snapshot > 0 ? number_format($this->price_snapshot) . ' ج.م' : 'حسب الفحص';
        } elseif ($this->service && $this->service->price !== null) {
            $costFormatted = $this->service->price > 0 ? number_format($this->service->price) . ' ج.م' : 'حسب الفحص';
        }

        $canCancel = in_array($this->status, ['pending', 'confirmed'], true)
            && $this->date
            && Carbon::createFromFormat(
                'Y-m-d H:i',
                Carbon::parse($this->date)->format('Y-m-d') . ' ' . substr($this->time, 0, 5),
                'Africa/Cairo'
            )->isFuture();

        return [
            'id' => $this->number ?: (string) $this->id,
            'dbId' => $this->id,
            'number' => $this->number,
            'userId' => $this->user_id,
            'userBikeId' => $this->user_bike_id,
            'bike' => $bikeTitle,
            'customModel' => $this->custom_model,
            'odometer' => $this->odometer,
            'serviceId' => $this->service_id,
            'service' => $this->service?->name ?: 'صيانة',
            'serviceName' => $this->service?->name ?: 'صيانة',
            'serviceObj' => new ServiceResource($this->whenLoaded('service')),
            'date' => $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null,
            'time' => $timeArabic ?: substr($this->time, 0, 5),
            'timeRaw' => substr($this->time, 0, 5),
            'timeArabic' => $timeArabic,
            'price' => $this->price_snapshot,
            'priceSnapshot' => $this->price_snapshot,
            'cost' => $costFormatted,
            'status' => $statusLabel,
            'rawStatus' => $this->status,
            'canCancel' => $canCancel,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
