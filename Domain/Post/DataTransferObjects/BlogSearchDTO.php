<?php

namespace Domain\Post\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class BlogSearchDTO implements DataTransferObjectInterface
{
    public function __construct(
        public string $term,
    ) {}

    /**
     * @return array{term: string}
     */
    public function toArray(): array
    {
        return [
            'term' => $this->term,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(term: trim($request->validated('q')));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(term: trim($data['term']));
    }
}
