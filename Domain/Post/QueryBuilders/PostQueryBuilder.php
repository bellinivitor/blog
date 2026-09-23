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
