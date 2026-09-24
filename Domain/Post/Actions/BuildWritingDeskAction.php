<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class BuildWritingDeskAction
{
    private const int LIMIT = 4;

    /**
     * What the author has in progress: drafts, last edited first, and
     * scheduled posts, next to go out first.
     *
     * @return array{drafts: Collection<int, Post>, scheduled: Collection<int, Post>}
     */
    public function __invoke(User $author): array
    {
        return [
            'drafts' => Post::query()
                ->ownedBy($author)
                ->drafts()
                ->latest('updated_at')
                ->limit(self::LIMIT)
                ->get(),
            'scheduled' => Post::query()
                ->ownedBy($author)
                ->scheduled()
                ->orderBy('published_at')
                ->limit(self::LIMIT)
                ->get(),
        ];
    }
}
