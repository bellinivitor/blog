<?php

namespace Domain\Post\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $resource The post's total of likes.
 */
class PostLikesResource extends JsonResource
{
    /**
     * @return array{likes: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'likes' => $this->resource,
        ];
    }
}
