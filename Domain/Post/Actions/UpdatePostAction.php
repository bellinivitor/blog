<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Domain\Post\DataTransferObjects\PostDTO;
use Domain\Shared\Actions\GenerateUniqueSlugAction;
use Domain\Tag\Actions\FindOrCreateTagsAction;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdatePostAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateUniqueSlugAction,
        private FindOrCreateTagsAction $findOrCreateTagsAction,
        private ListReservedSlugsAction $listReservedSlugs,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(Post $post, PostDTO $postDTO): Post
    {
        try {
            DB::beginTransaction();

            $post->fill([
                'title' => $postDTO->title,
                'slug' => $postDTO->slug ?? ($this->generateUniqueSlugAction)($postDTO->title, Post::class, $post->id, ($this->listReservedSlugs)()),
                'excerpt' => $postDTO->excerpt,
                'content' => $postDTO->content,
            ]);
            $post->save();

            $post->tags()->sync(array_unique([
                ...$postDTO->tagIds,
                ...($this->findOrCreateTagsAction)($postDTO->newTagNames),
            ]));

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $post;
    }
}
