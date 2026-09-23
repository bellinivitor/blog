<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\Post\PostDailyView;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

readonly class BuildReadingStatsAction
{
    public const int PERIOD_DAYS = 30;

    private const int MOST_READ_LIMIT = 5;

    /**
     * KPIs of the author's blog for the dashboard. Views are compared between
     * the last PERIOD_DAYS days (today included, blog timezone) and the
     * PERIOD_DAYS before them.
     *
     * @return array{
     *     viewsInPeriod: int,
     *     viewsInPreviousPeriod: int,
     *     dailyViews: array<int, array{date: string, views: int}>,
     *     totalViews: int,
     *     publishedCount: int,
     *     draftCount: int,
     *     scheduledCount: int,
     *     lastPublishedAt: CarbonImmutable|null,
     *     mostRead: Collection<int, Post>,
     * }
     */
    public function __invoke(User $author): array
    {
        $today = CarbonImmutable::now(config('blog.timezone'))->startOfDay();
        $periodStart = $today->subDays(self::PERIOD_DAYS - 1);
        $previousStart = $periodStart->subDays(self::PERIOD_DAYS);

        /** @var array<string, int|string> $viewsByDate */
        $viewsByDate = PostDailyView::query()
            ->whereIn('post_id', Post::query()->ownedBy($author)->select('id'))
            ->whereBetween('date', [$previousStart->toDateString(), $today->toDateString()])
            ->groupBy('date')
            ->selectRaw('date, SUM(views) as total')
            ->pluck('total', 'date')
            ->all();

        $dailyViews = [];
        $previousViews = 0;

        for ($day = $previousStart; $day->lte($today); $day = $day->addDay()) {
            $views = (int) ($viewsByDate[$day->toDateString()] ?? 0);

            if ($day->lt($periodStart)) {
                $previousViews += $views;
            } else {
                $dailyViews[] = ['date' => $day->toDateString(), 'views' => $views];
            }
        }

        $published = Post::query()->ownedBy($author)->published();
        $lastPublishedAt = (clone $published)->max('published_at');

        return [
            'viewsInPeriod' => array_sum(array_column($dailyViews, 'views')),
            'viewsInPreviousPeriod' => $previousViews,
            'dailyViews' => $dailyViews,
            'totalViews' => (int) (clone $published)->sum('views_count'),
            'publishedCount' => (clone $published)->count(),
            'draftCount' => Post::query()->ownedBy($author)->drafts()->count(),
            'scheduledCount' => Post::query()->ownedBy($author)->scheduled()->count(),
            'lastPublishedAt' => $lastPublishedAt !== null ? CarbonImmutable::parse($lastPublishedAt) : null,
            'mostRead' => $published
                ->where('views_count', '>', 0)
                ->orderByDesc('views_count')
                ->limit(self::MOST_READ_LIMIT)
                ->get(),
        ];
    }
}
