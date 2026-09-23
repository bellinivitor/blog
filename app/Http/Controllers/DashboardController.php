<?php

namespace App\Http\Controllers;

use App\Models\Post\Post;
use Domain\Post\Actions\BuildReadingStatsAction;
use Domain\Post\Resources\MostReadPostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Greeting, shortcuts and the KPIs of the author's blog.
     */
    public function __invoke(Request $request, BuildReadingStatsAction $buildReadingStats): Response
    {
        Gate::authorize('viewAny', Post::class);

        $stats = $buildReadingStats($request->user());

        return Inertia::render('Dashboard', [
            'periodDays' => BuildReadingStatsAction::PERIOD_DAYS,
            'viewsInPeriod' => $stats['viewsInPeriod'],
            'viewsInPreviousPeriod' => $stats['viewsInPreviousPeriod'],
            'dailyViews' => $stats['dailyViews'],
            'totalViews' => $stats['totalViews'],
            'publishedCount' => $stats['publishedCount'],
            'draftCount' => $stats['draftCount'],
            'scheduledCount' => $stats['scheduledCount'],
            'lastPublishedAt' => $stats['lastPublishedAt']?->toIso8601String(),
            'mostRead' => MostReadPostResource::collection($stats['mostRead']),
        ]);
    }
}
