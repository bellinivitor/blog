<?php

namespace Domain\Tag\QueryBuilders;

use App\Models\Tag\Tag;
use Domain\Tag\DataTransferObjects\TagSearchDTO;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Tag>
 */
class TagQueryBuilder extends Builder
{
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
