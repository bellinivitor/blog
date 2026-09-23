<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Domain\Post\DataTransferObjects\PostDTO;
use Domain\Shared\Actions\GenerateUniqueSlugAction;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdatePostAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateUniqueSlugAction,
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
                'slug' => $postDTO->slug ?? ($this->generateUniqueSlugAction)($postDTO->title, Post::class, $post->id),
                'excerpt' => $postDTO->excerpt,
                'content' => $postDTO->content,
            ]);
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
