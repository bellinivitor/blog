<?php

namespace Domain\Tag\Actions;

use App\Models\Tag\Tag;
use Domain\Shared\Actions\GenerateUniqueSlugAction;
use Domain\Tag\DataTransferObjects\TagDTO;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class StoreTagAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateUniqueSlugAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(TagDTO $tagDTO): Tag
    {
        try {
            DB::beginTransaction();

            $tag = new Tag;
            $tag->fill([
                'name' => $tagDTO->name,
                'slug' => $tagDTO->slug ?? ($this->generateUniqueSlugAction)($tagDTO->name, Tag::class),
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
