<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\Post\PostDailyView;
use Carbon\CarbonImmutable;
use Domain\Post\DataTransferObjects\PostViewDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class RecordPostViewAction
{
    /**
     * User agents of crawlers, link-preview fetchers and command line tools.
     */
    private const string NON_HUMAN_AGENTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|whatsapp|telegram|curl|wget|python|headless/i';

    /**
     * Count one view of a public post, in its all-time total and in today's
     * row (blog timezone) for the dashboard trends. The author (any signed-in
     * user) and non-human agents are ignored, and a reader counts once per
     * post per day. The increment bypasses Eloquent so updated_at (and the
     * caches keyed on it) stay untouched.
     */
    public function __invoke(Post $post, PostViewDTO $viewDTO): bool
    {
        if ($viewDTO->fromAuthenticatedUser || $this->isNonHuman($viewDTO->userAgent)) {
            return false;
        }

        $now = CarbonImmutable::now(config('blog.timezone'));

        if (! $this->isFirstViewToday($post, $viewDTO, $now)) {
            return false;
        }

        DB::transaction(function () use ($post, $now): void {
            Post::query()->whereKey($post->id)->toBase()->increment('views_count');

            PostDailyView::query()->toBase()->upsert(
                [['post_id' => $post->id, 'date' => $now->toDateString(), 'views' => 1]],
                ['post_id', 'date'],
                ['views' => DB::raw('post_daily_views.views + 1')],
            );
        });

        return true;
    }

    /**
     * Recognise a returning reader without storing who they are: IP and user
     * agent are hashed with a random salt that lives only until midnight
     * (DiscardPostViewSaltAction removes it right after). The IP is never
     * stored, the hash only sits in the cache until the end of the day, and
     * without the salt it cannot be traced back or linked to other days.
     */
    private function isFirstViewToday(Post $post, PostViewDTO $viewDTO, CarbonImmutable $now): bool
    {
        $day = $now->toDateString();
        $endOfDay = $now->endOfDay();
        $saltKey = self::saltKey($day);

        Cache::add($saltKey, bin2hex(random_bytes(16)), $endOfDay);

        $visitor = hash('sha256', Cache::get($saltKey).'|'.$viewDTO->ipAddress.'|'.$viewDTO->userAgent);

        return Cache::add("post-views:{$day}:{$post->id}:{$visitor}", true, $endOfDay);
    }

    public static function saltKey(string $day): string
    {
        return "post-views:salt:{$day}";
    }

    private function isNonHuman(?string $userAgent): bool
    {
        return $userAgent === null || $userAgent === '' || preg_match(self::NON_HUMAN_AGENTS, $userAgent) === 1;
    }
}
