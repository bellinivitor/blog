<?php

namespace Domain\Post\Actions;

use Domain\Post\DataTransferObjects\PostImageDTO;
use Domain\Post\Exceptions\PostImageUploadFailedException;
use Illuminate\Support\Facades\Storage;

readonly class StorePostImageAction
{
    public const string DISK = 'public';

    public const string DIRECTORY = 'posts';

    /**
     * Store an image used inside post content and return its public URL.
     */
    public function __invoke(PostImageDTO $postImageDTO): string
    {
        $path = $postImageDTO->image->store(self::DIRECTORY, self::DISK);

        if ($path === false) {
            throw new PostImageUploadFailedException;
        }

        return Storage::disk(self::DISK)->url($path);
    }
}
