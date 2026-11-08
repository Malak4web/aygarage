<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => 'rev' . $this->id,
            'dbId' => $this->id,
            'author' => $this->author_name,
            'authorName' => $this->author_name,
            'rating' => (int) $this->rating,
            'content' => $this->body,
            'body' => $this->body,
            'date' => $this->created_at ? $this->created_at->locale('ar')->diffForHumans() : '',
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
