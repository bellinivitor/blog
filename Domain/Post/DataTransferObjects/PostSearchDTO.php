<?php

namespace Domain\Post\DataTransferObjects;

use Domain\Post\Enums\PostStatus;
use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class PostSearchDTO implements DataTransferObjectInterface
{
    public function __construct(
        public ?string $search = null,
        public ?PostStatus $status = null,
        public ?int $tagId = null,
        public bool $trashed = false,
    ) {}

    /**
     * @return array{search: string|null, status: string|null, tag_id: int|null, trashed: bool}
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status?->value,
            'tag_id' => $this->tagId,
            'trashed' => $this->trashed,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(
            search: $request->validated('search'),
            status: $request->enum('status', PostStatus::class),
            tagId: $request->validated('tag_id') !== null ? (int) $request->validated('tag_id') : null,
            trashed: $request->boolean('trashed'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            search: $data['search'] ?? null,
            status: isset($data['status']) ? PostStatus::from($data['status']) : null,
            tagId: isset($data['tag_id']) ? (int) $data['tag_id'] : null,
            trashed: (bool) ($data['trashed'] ?? false),
        );
    }
}
