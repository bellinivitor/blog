<?php

namespace Domain\Post\DataTransferObjects;

use Illuminate\Http\Request;

/**
 * Who is reading a post, as far as the view counter cares. Built from the
 * plain page request (there is no FormRequest), so it does not implement
 * DataTransferObjectInterface.
 */
final readonly class PostViewDTO
{
    public function __construct(
        public ?string $userAgent,
        public bool $fromAuthenticatedUser,
    ) {}

    public static function fromHttpRequest(Request $request): self
    {
        return new self(
            userAgent: $request->userAgent(),
            fromAuthenticatedUser: $request->user() !== null,
        );
    }
}
