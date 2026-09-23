<?php

namespace Domain\Tag\QueryBuilders;

use App\Models\Tag\Tag;
use Domain\Post\QueryBuilders\PostQueryBuilder;
use Domain\Tag\DataTransferObjects\TagSearchDTO;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Tag>
 */
class TagQueryBuilder extends Builder
{
    /**
     * Tags with at least one post visible on the public blog.
     */
    public function withPublishedPosts(): static
    {
        $this->whereHas('posts', fn (PostQueryBuilder $query) => $query->published());

        return $this;
    }

    public function search(TagSearchDTO $searchDTO): static
    {
        if ($searchDTO->search) {
            $this->where('name', 'like', '%'.str_replace(' ', '%', $searchDTO->search).'%');
        }

        if ($searchDTO->trashed) {
            $this->onlyTrashed();
        }

        return $this;
    }
}
