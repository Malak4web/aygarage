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
            try {
                $parsed = Carbon::createFromFormat('H:i', substr($this->time, 0, 5));
                if ($parsed) {
                    $hour = (int) $parsed->format('g');
                    $minute = $parsed->format('i');
                    $ampm = $parsed->format('A') === 'AM' ? 'ص' : 'م';
                    $timeArabic = "{$hour}:{$minute} {$ampm}";
                }
            } catch (\Throwable $e) {
                $timeArabic = (string) $this->time;
            }
        }

        $dateStr = null;
        $dateArabic = null;
        if ($this->date) {
            try {
                $parsedDate = Carbon::parse($this->date)->locale('ar');
                $dateStr = $parsedDate->format('Y-m-d');
                $dayName = $parsedDate->translatedFormat('l');
                $dayNum = $parsedDate->format('j');
                $monthName = $parsedDate->translatedFormat('F');
                $dateArabic = "{$dayName} {$dayNum} {$monthName}";
            } catch (\Throwable $e) {
                $dateStr = (string) $this->date;
                $dateArabic = (string) $this->date;
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

        $canCancel = false;
        if (in_array($this->status, ['pending', 'confirmed'], true) && $this->date && $this->time) {
            try {
                $canCancel = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    Carbon::parse($this->date)->format('Y-m-d') . ' ' . substr($this->time, 0, 5),
                    'Africa/Cairo'
                )->isFuture();
            } catch (\Throwable $e) {
                $canCancel = false;
            }
        }

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
            'date' => $dateArabic ?: $dateStr,
            'dateRaw' => $dateStr,
            'dateFormatted' => $dateArabic ?: $dateStr,
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
