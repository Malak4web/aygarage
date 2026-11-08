<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $priceFormatted = $this->price_label;
        if (!$priceFormatted) {
            $priceFormatted = $this->price ? number_format($this->price) . ' ج.م' : 'حسب الفحص';
        }

        $desc = $this->duration_label;
        if ($this->price_label) {
            $desc .= ' · ' . $this->price_label;
        } elseif ($this->price) {
            $desc .= ' · ' . number_format($this->price) . ' ج.م';
        }

        return [
            'id' => $this->code,
            'dbId' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'duration' => $this->duration_label,
            'price' => (int) ($this->price ?? 0),
            'priceFormatted' => $priceFormatted,
            'desc' => $desc,
        ];
    }
}
