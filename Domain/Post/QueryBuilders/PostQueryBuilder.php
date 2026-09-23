<?php

namespace Domain\Post\QueryBuilders;

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\Actions\NormalizeSearchTextAction;
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
     * Posts marked as published whose publication date is still ahead.
     */
    public function scheduled(): static
    {
        $this->where('status', PostStatus::Published)
            ->where('published_at', '>', now());

        return $this;
    }

    public function drafts(): static
    {
        $this->where('status', PostStatus::Draft);

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
     * Published posts, other than the given one, sharing at least one tag.
     */
    public function sharingTagsWith(Post $post): static
    {
        $this->published()
            ->whereKeyNot($post->id)
            ->whereHas('tags', fn (Builder $query) => $query->whereKey($post->tags->modelKeys()));

        return $this;
    }

    /**
     * Posts whose title or content contains the term, ignoring accents and
     * case, title matches first. LIKE wildcards typed by the reader are
     * matched literally.
     */
    public function matchingText(BlogSearchDTO $searchDTO): static
    {
        $term = app(NormalizeSearchTextAction::class)($searchDTO->term);
        $pattern = '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';

        $this->where(fn (Builder $query) => $query
            ->whereRaw("search_title like ? escape '!'", [$pattern])
            ->orWhereRaw("search_content like ? escape '!'", [$pattern]))
            ->orderByRaw("case when search_title like ? escape '!' then 0 else 1 end", [$pattern]);

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
