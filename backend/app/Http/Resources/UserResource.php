<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryAddress = $this->addresses ? ($this->addresses->firstWhere('is_default', true) ?: $this->addresses->first()) : null;

        return [
            'id' => (string) $this->id,
            'dbId' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role ?? 'customer',
            'address' => $primaryAddress ? [
                'id' => $primaryAddress->id,
                'governorate' => $primaryAddress->governorate,
                'area' => $primaryAddress->area,
                'street' => $primaryAddress->street,
                'isDefault' => (bool) $primaryAddress->is_default,
            ] : null,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses', $this->addresses ?? [])),
            'savedBikes' => UserBikeResource::collection($this->whenLoaded('bikes', $this->bikes ?? [])),
            'orders' => [],
            'bookings' => [],
        ];
    }
}
