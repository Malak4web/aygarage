<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBikeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'licenseUntil' => $this->license_until,
            'license' => $this->license_until ?: '2027',
            'color' => $this->color,
            'col' => $this->color ?: '#2e2e2e',
            'lastVisitAt' => $this->last_visit_at,
            'lastVisit' => $this->last_visit_at ? date('Y-m-d', strtotime($this->last_visit_at)) : 'لا توجد زيارات سابقة',
        ];
    }
}
