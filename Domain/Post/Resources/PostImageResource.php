<?php

namespace Domain\Post\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $resource The public URL of the stored image.
 */
class PostImageResource extends JsonResource
{
    /**
     * @return array{url: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->resource,
        ];
    }
}
