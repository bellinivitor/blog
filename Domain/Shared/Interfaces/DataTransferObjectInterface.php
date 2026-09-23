<?php

namespace Domain\Shared\Interfaces;

use Illuminate\Foundation\Http\FormRequest;

interface DataTransferObjectInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public static function fromRequest(FormRequest $request): static;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;
}
