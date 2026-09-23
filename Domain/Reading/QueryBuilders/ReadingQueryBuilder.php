<?php

namespace Domain\Reading\QueryBuilders;

use App\Models\Reading\Reading;
use Domain\Reading\DataTransferObjects\ReadingSearchDTO;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Reading>
 */
class ReadingQueryBuilder extends Builder
{
    public function search(ReadingSearchDTO $searchDTO): static
    {
        if ($searchDTO->search) {
            $this->where('title', 'like', '%'.str_replace(' ', '%', $searchDTO->search).'%');
        }

        if ($searchDTO->trashed) {
            $this->onlyTrashed();
        }

        return $this;
    }

    /**
     * Newest first, the id breaking ties between readings added together.
     */
    public function latestFirst(): static
    {
        $this->orderByDesc('created_at')->orderByDesc('id');

        return $this;
    }
}
