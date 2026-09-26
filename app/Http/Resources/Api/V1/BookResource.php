<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Book情報
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_at' => $this->published_at,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // ジャンル情報（リレーション）
            'genres' => GenreResource::collection($this->whenLoaded('genres')),

            // レビューの平均値
            'average_rating' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => $this->reviews->avg('rating')
            ),

            // レビュー件数
            'reviews_count' => $this->whenLoaded('reviews', fn() => $this->reviews->count()),

            // レビュー詳細一覧
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
