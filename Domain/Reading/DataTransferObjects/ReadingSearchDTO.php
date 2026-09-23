<?php

namespace Domain\Reading\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class ReadingSearchDTO implements DataTransferObjectInterface
{
    public function __construct(
        public ?string $search = null,
        public bool $trashed = false,
    ) {}

    /**
     * @return array{search: string|null, trashed: bool}
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'trashed' => $this->trashed,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(
            search: $request->validated('search'),
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
            trashed: (bool) ($data['trashed'] ?? false),
        );
    }
}
