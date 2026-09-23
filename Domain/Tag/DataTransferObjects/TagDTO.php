<?php

namespace Domain\Tag\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class TagDTO implements DataTransferObjectInterface
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
    ) {}

    /**
     * @return array{name: string, slug: string|null}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'] ?? null,
        );
    }
}
