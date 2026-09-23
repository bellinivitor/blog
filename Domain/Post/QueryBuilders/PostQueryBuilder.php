<?php

namespace Domain\Post\QueryBuilders;

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\DataTransferObjects\PostSearchDTO;
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
