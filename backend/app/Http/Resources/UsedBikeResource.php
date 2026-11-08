<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UsedBikeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images->map(function ($img) {
            $path = $img->path;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return Storage::disk('public')->url($path);
        })->values()->all();

        $inspections = $this->inspections->map(function ($ins) {
            return [
                'section' => $ins->section,
                'score' => (int) $ins->score,
                'note' => $ins->note,
                'warning' => (bool) $ins->is_warning,
            ];
        })->values()->all();

        $serviceHistory = $this->serviceRecords->map(function ($rec) {
            return [
                'date' => Carbon::parse($rec->date)->locale('ar')->isoFormat('D MMMM YYYY'),
                'rawDate' => Carbon::parse($rec->date)->format('Y-m-d'),
                'odo' => number_format($rec->odometer) . ' كم',
                'odometer' => (int) $rec->odometer,
                'service' => $rec->work,
                'tech' => $rec->technician,
            ];
        })->values()->all();

        $data = [
            'id' => 'b' . $this->id,
            'dbId' => $this->id,
            'slug' => $this->slug,
            'model' => $this->model_name,
            'bikeModelCode' => $this->bike_model_code,
            'family' => $this->family,
            'year' => (int) $this->year,
            'km' => (int) $this->km,
            'price' => (int) $this->price,
            'installmentMonthly' => (int) $this->installment_monthly,
            'inspectionScore' => (int) $this->inspection_score,
            'col' => $this->color,
            'color' => $this->color,
            'owner' => $this->owner_label,
            'ownerLabel' => $this->owner_label,
            'licenseUntil' => $this->license_until,
            'soldCount' => (int) $this->sold_count,
            'notes' => $this->notes,
            'specs' => $this->specs ?? (object)[],
            'status' => $this->status,
            'inspection' => $inspections,
            'serviceHistory' => $serviceHistory,
            'images' => $images,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];

        if (isset($this->similar)) {
            $data['similar'] = UsedBikeResource::collection($this->similar);
        }

        return $data;
    }
}
