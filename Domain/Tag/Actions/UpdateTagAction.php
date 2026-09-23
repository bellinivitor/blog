<?php

namespace Domain\Tag\Actions;

use App\Models\Tag\Tag;
use Domain\Shared\Actions\GenerateUniqueSlugAction;
use Domain\Tag\DataTransferObjects\TagDTO;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateTagAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateUniqueSlugAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(Tag $tag, TagDTO $tagDTO): Tag
    {
        try {
            DB::beginTransaction();

            $tag->fill([
                'name' => $tagDTO->name,
                'slug' => $tagDTO->slug ?? ($this->generateUniqueSlugAction)($tagDTO->name, Tag::class, $tag->id),
            ]);
            $tag->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $tag;
    }
}
