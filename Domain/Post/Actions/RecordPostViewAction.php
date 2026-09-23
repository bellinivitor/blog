<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Domain\Post\DataTransferObjects\PostViewDTO;

readonly class RecordPostViewAction
{
    /**
     * User agents of crawlers, link-preview fetchers and command line tools.
     */
    private const string NON_HUMAN_AGENTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|whatsapp|telegram|curl|wget|python|headless/i';

    /**
     * Count one view of a public post. The author (any signed-in user) and
     * non-human agents are ignored; nothing about the reader is stored.
     * The increment bypasses Eloquent so updated_at (and the caches keyed on
     * it) stay untouched.
     */
    public function __invoke(Post $post, PostViewDTO $viewDTO): bool
    {
        if ($viewDTO->fromAuthenticatedUser || $this->isNonHuman($viewDTO->userAgent)) {
            return false;
        }

        Post::query()->whereKey($post->id)->toBase()->increment('views_count');

        return true;
    }

    private function isNonHuman(?string $userAgent): bool
    {
        return $userAgent === null || $userAgent === '' || preg_match(self::NON_HUMAN_AGENTS, $userAgent) === 1;
    }
}
