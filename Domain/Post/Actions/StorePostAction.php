<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\DataTransferObjects\PostDTO;
use Domain\Post\Enums\PostStatus;
use Domain\Shared\Actions\GenerateUniqueSlugAction;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class StorePostAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateUniqueSlugAction,
    ) {}

    /**
     * Create a post as a draft owned by the given author.
     *
     * @throws Throwable
     */
    public function __invoke(PostDTO $postDTO, User $author): Post
    {
        try {
            DB::beginTransaction();

            $post = new Post;
            $post->fill([
                'title' => $postDTO->title,
                'slug' => $postDTO->slug ?? ($this->generateUniqueSlugAction)($postDTO->title, Post::class),
                'excerpt' => $postDTO->excerpt,
                'content' => $postDTO->content,
            ]);
            $post->status = PostStatus::Draft;
            $post->author()->associate($author);
            $post->save();

            $post->tags()->sync($postDTO->tagIds);

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $post;
    }
}
