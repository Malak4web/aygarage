<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBikeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestRecord = $this->latestServiceRecord;
        $lastVisitDate = null;
        $lastVisitFormatted = null;

        if ($latestRecord && $latestRecord->date) {
            $parsedDate = Carbon::parse($latestRecord->date);
            $lastVisitDate = $parsedDate->toDateString();
            $lastVisitFormatted = $parsedDate->locale('ar')->isoFormat('D MMMM YYYY');
        }

        return [
            'id' => (string) $this->id,
            'dbId' => $this->id,
            'model' => $this->model_name ?: ('Zontes ' . $this->bike_model_code),
            'bikeModelCode' => $this->bike_model_code,
            'modelName' => $this->model_name,
            'year' => (int) $this->year,
            'plate' => $this->plate,
            'odometer' => (int) $this->odometer,
            'odo' => (int) $this->odometer,
            'nextServiceKm' => (int) $this->next_service_km,
            'nextService' => (int) $this->next_service_km,
            'licenseUntil' => $this->license_until ?: null,
            'license' => $this->license_until ?: null,
            'color' => $this->color,
            'col' => $this->color ?: '#2e2e2e',
            'lastVisitAt' => $lastVisitDate,
            'lastVisit' => $lastVisitFormatted,
        ];
    }
}
