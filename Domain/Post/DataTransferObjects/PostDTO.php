<?php

namespace Domain\Post\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class PostDTO implements DataTransferObjectInterface
{
    /**
     * @param  array<int, int>  $tagIds
     */
    public function __construct(
        public string $title,
        public string $content,
        public ?string $slug = null,
        public ?string $excerpt = null,
        public array $tagIds = [],
    ) {}

    /**
     * @return array{title: string, content: string, slug: string|null, excerpt: string|null, tag_ids: array<int, int>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'tag_ids' => $this->tagIds,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(
            title: $request->validated('title'),
            content: $request->validated('content'),
            slug: $request->validated('slug'),
            excerpt: $request->validated('excerpt'),
            tagIds: array_map('intval', $request->validated('tag_ids', [])),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            tagIds: array_map('intval', $data['tag_ids'] ?? []),
        );
    }
}
