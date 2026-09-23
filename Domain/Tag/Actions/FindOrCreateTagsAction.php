<?php

namespace Domain\Tag\Actions;

use App\Models\Tag\Tag;
use Domain\Tag\DataTransferObjects\TagDTO;
use Throwable;

readonly class FindOrCreateTagsAction
{
    public function __construct(
        private StoreTagAction $storeTagAction,
    ) {}

    /**
     * Resolve tag names to ids, reusing active tags with the same name
     * (case-insensitive) and creating the missing ones.
     *
     * @param  array<int, string>  $names
     * @return array<int, int>
     *
     * @throws Throwable
     */
    public function __invoke(array $names): array
    {
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $tag = Tag::query()->whereRaw('lower(name) = ?', [mb_strtolower($name)])->first()
                ?? ($this->storeTagAction)(new TagDTO(name: $name));

            $ids[] = $tag->id;
        }

        return array_values(array_unique($ids));
    }
}
