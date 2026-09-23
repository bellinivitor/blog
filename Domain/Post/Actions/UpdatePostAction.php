<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Domain\Post\DataTransferObjects\PostDTO;
use Domain\Post\Enums\PostStatus;
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

            if ($this->isRevision($post)) {
                $post->revised_at = now();
            }

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

    /**
     * A change to the text of a post readers have already seen. Fixes made
     * on the day it went out are part of publishing, not a revision.
     */
    private function isRevision(Post $post): bool
    {
        return $post->status === PostStatus::Published
            && $post->published_at?->addDay()->isPast()
            && $post->isDirty(['title', 'content']);
    }
}
