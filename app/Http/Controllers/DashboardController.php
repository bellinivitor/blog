<?php

namespace App\Http\Controllers;

use App\Models\Post\Post;
use Domain\Post\Actions\BuildReadingStatsAction;
use Domain\Post\Actions\BuildWritingDeskAction;
use Domain\Post\Resources\MostReadPostResource;
use Domain\Post\Resources\PostSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The author's posts in progress and the KPIs of their blog.
     */
    public function __invoke(Request $request, BuildReadingStatsAction $buildReadingStats, BuildWritingDeskAction $buildWritingDesk): Response
    {
        Gate::authorize('viewAny', Post::class);

        $stats = $buildReadingStats($request->user());
        $desk = $buildWritingDesk($request->user());

        return Inertia::render('Dashboard', [
            'periodDays' => BuildReadingStatsAction::PERIOD_DAYS,
            'viewsInPeriod' => $stats['viewsInPeriod'],
            'viewsInPreviousPeriod' => $stats['viewsInPreviousPeriod'],
            'dailyViews' => $stats['dailyViews'],
            'totalViews' => $stats['totalViews'],
            'totalLikes' => $stats['totalLikes'],
            'publishedCount' => $stats['publishedCount'],
            'draftCount' => $stats['draftCount'],
            'scheduledCount' => $stats['scheduledCount'],
            'lastPublishedAt' => $stats['lastPublishedAt']?->toIso8601String(),
            'mostRead' => MostReadPostResource::collection($stats['mostRead']),
            'drafts' => PostSummaryResource::collection($desk['drafts']),
            'scheduled' => PostSummaryResource::collection($desk['scheduled']),
        ]);
    }
}
