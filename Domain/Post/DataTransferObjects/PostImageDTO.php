<?php

namespace Domain\Post\DataTransferObjects;

use Domain\Shared\Interfaces\DataTransferObjectInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

final readonly class PostImageDTO implements DataTransferObjectInterface
{
    public function __construct(
        public UploadedFile $image,
    ) {}

    /**
     * @return array{image: UploadedFile}
     */
    public function toArray(): array
    {
        return [
            'image' => $this->image,
        ];
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var UploadedFile $image */
        $image = $request->file('image');

        return new self(image: $image);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(image: $data['image']);
    }
}
