<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Collection;

readonly class FindReadingSuggestionsAction
{
    /**
     * Published posts to offer a reader who hit a dead end: the most read
     * first, the newest breaking ties (and filling in before any views exist).
     *
     * @return Collection<int, Post>
     */
    public function __invoke(int $limit): Collection
    {
        return Post::query()
            ->published()
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
