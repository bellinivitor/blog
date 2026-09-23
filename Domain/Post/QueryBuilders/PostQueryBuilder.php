<?php

namespace Domain\Post\QueryBuilders;

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\DataTransferObjects\BlogSearchDTO;
use Domain\Post\DataTransferObjects\PostSearchDTO;
use Domain\Post\Enums\PostStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Post>
 */
class PostQueryBuilder extends Builder
{
    public function ownedBy(User $user): static
    {
        $this->where('author_id', $user->id);

        return $this;
    }

    /**
     * Posts visible on the public blog: published and not scheduled for later.
     */
    public function published(): static
    {
        $this->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        return $this;
    }

    /**
     * The published post right before the given one (older), by publication
     * date with the id as tie-breaker.
     */
    public function publishedBefore(Post $post): static
    {
        $this->published()
            ->where(fn (Builder $query) => $query
                ->where('published_at', '<', $post->published_at)
                ->orWhere(fn (Builder $query) => $query
                    ->where('published_at', $post->published_at)
                    ->where('id', '<', $post->id)))
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        return $this;
    }

    /**
     * The published post right after the given one (newer).
     */
    public function publishedAfter(Post $post): static
    {
        $this->published()
            ->where(fn (Builder $query) => $query
                ->where('published_at', '>', $post->published_at)
                ->orWhere(fn (Builder $query) => $query
                    ->where('published_at', $post->published_at)
                    ->where('id', '>', $post->id)))
            ->orderBy('published_at')
            ->orderBy('id');

        return $this;
    }

    /**
     * Published posts sharing tags with the given one, most shared tags
     * first, then newest.
     */
    public function relatedTo(Post $post): static
    {
        $tagIds = $post->tags->modelKeys();

        $this->published()
            ->whereKeyNot($post->id)
            ->whereHas('tags', fn (Builder $query) => $query->whereKey($tagIds))
            ->withCount(['tags as shared_tags_count' => fn (Builder $query) => $query->whereKey($tagIds)])
            ->orderByDesc('shared_tags_count')
            ->orderByDesc('published_at');

        return $this;
    }

    /**
     * Posts whose title or content contains the term, title matches first.
     * LIKE wildcards typed by the reader are matched literally.
     */
    public function matchingText(BlogSearchDTO $searchDTO): static
    {
        $pattern = '%'.strtr($searchDTO->term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';

        $this->where(fn (Builder $query) => $query
            ->whereRaw("title like ? escape '!'", [$pattern])
            ->orWhereRaw("content like ? escape '!'", [$pattern]))
            ->orderByRaw("case when title like ? escape '!' then 0 else 1 end", [$pattern]);

        return $this;
    }

    public function search(PostSearchDTO $searchDTO): static
    {
        if ($searchDTO->search) {
            $this->where('title', 'like', '%'.str_replace(' ', '%', $searchDTO->search).'%');
        }

        if ($searchDTO->status) {
            $this->where('status', $searchDTO->status);
        }

        if ($searchDTO->tagId) {
            $this->whereHas('tags', fn (Builder $query) => $query->whereKey($searchDTO->tagId));
        }

        if ($searchDTO->trashed) {
            $this->onlyTrashed();
        }

        return $this;
    }
}
