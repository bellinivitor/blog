<?php

namespace Domain\Reading\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;

final readonly class ReadingDTO implements DataTransferObjectInterface
{
    public function __construct(
        public string $title,
        public string $url,
    ) {}

    /**
     * @return array{title: string, url: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        return new self(
            title: $request->validated('title'),
            url: $request->validated('url'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            title: $data['title'],
            url: $data['url'],
        );
    }
}
