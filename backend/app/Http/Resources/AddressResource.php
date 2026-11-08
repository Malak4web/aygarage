<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'governorate' => $this->governorate,
            'area' => $this->area,
            'street' => $this->street,
            'isDefault' => (bool) $this->is_default,
        ];
    }
}
