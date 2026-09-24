<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\Enums\PostStatus;

readonly class CountAuthorPostsAction
{
    /**
     * How many posts the author has in each tab of the post list. Published
     * includes scheduled posts, as the status filter does.
     *
     * @return array{all: int, draft: int, published: int, trashed: int}
     */
    public function __invoke(User $author): array
    {
        /** @var array<string, int|string> $byStatus */
        $byStatus = Post::query()
            ->ownedBy($author)
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as total')
            ->pluck('total', 'status')
            ->all();

        $draft = (int) ($byStatus[PostStatus::Draft->value] ?? 0);
        $published = (int) ($byStatus[PostStatus::Published->value] ?? 0);

        return [
            'all' => $draft + $published,
            'draft' => $draft,
            'published' => $published,
            'trashed' => Post::query()->ownedBy($author)->onlyTrashed()->count(),
        ];
    }
}
