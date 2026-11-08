<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $slug = $product?->slug;
        $lineId = ($slug ?: $this->product_id) . '_' . ($this->option_code ?: 'default');
        $primaryImage = $product?->images?->first()?->path;

        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'optionCode' => $this->option_code,
            'name' => $this->name_snapshot,
            'unitPrice' => $this->unit_price,
            'qty' => $this->qty,
            'lineTotal' => $this->line_total,
            'lineId' => $lineId,
            'selectedOption' => $this->option_code ? [
                'code' => $this->option_code,
                'price' => $this->unit_price,
            ] : null,
            'product' => [
                'id' => $product?->id ?? $this->product_id,
                'slug' => $slug,
                'name' => $this->name_snapshot,
                'price' => $this->unit_price,
                'type' => $product?->type ?? 'oil',
                'color' => $product?->color ?? null,
                'image' => $primaryImage ? url('storage/' . $primaryImage) : null,
            ],
        ];
    }
}
