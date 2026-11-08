<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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

        $data = [
            'id' => 'p' . $this->id,
            'dbId' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'brand' => $this->brand,
            'sku' => $this->sku,
            'category' => $this->category ? $this->category->slug : null,
            'categoryName' => $this->category ? $this->category->name : null,
            'type' => $this->type,
            'color' => $this->color,
            'price' => (int) $this->price,
            'oldPrice' => $this->old_price ? (int) $this->old_price : null,
            'stock' => (int) $this->stock,
            'rating' => (float) $this->rating,
            'reviewCount' => (int) $this->review_count,
            'soldCount' => (int) $this->sold_count,
            'badge' => $this->badge,
            'isBestSeller' => (bool) $this->is_best_seller,
            'isWeeklyOffer' => (bool) $this->is_weekly_offer,
            'viscosity' => $this->viscosity,
            'oilType' => $this->oil_type,
            'fitNote' => $this->fit_note,
            'fits' => $this->fits ?? [],
            'description' => $this->description,
            'specs' => $this->specs ?? (object)[],
            'options' => ProductOptionResource::collection($this->options),
            'images' => $images,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];

        if (isset($this->related)) {
            $data['related'] = ProductResource::collection($this->related);
        }

        return $data;
    }
}
