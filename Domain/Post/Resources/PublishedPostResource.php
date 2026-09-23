<?php

namespace Domain\Post\Resources;

use App\Models\Post\Post;
use Domain\Tag\Resources\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public representation of a post on the blog.
 *
 * @mixin Post
 */
class PublishedPostResource extends JsonResource
{
    private const int WORDS_PER_MINUTE = 200;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'published_at' => $this->published_at?->toIso8601String(),
            'reading_minutes' => $this->readingMinutes(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }

    private function readingMinutes(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($this->content)), flags: PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / self::WORDS_PER_MINUTE));
    }
}
