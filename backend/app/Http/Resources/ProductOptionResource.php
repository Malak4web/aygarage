<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->code,
            'code' => $this->code,
            'label' => $this->label,
            'price' => (int) $this->price,
            'oldPrice' => $this->old_price ? (int) $this->old_price : null,
            'sort' => (int) $this->sort,
        ];
    }
}
