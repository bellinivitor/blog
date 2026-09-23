<?php

namespace App\Http\Controllers;

use App\Models\Post\Post;
use Domain\Post\Resources\MostReadPostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const int MOST_READ_LIMIT = 5;

    /**
     * Greeting, shortcuts and reading stats of the author's published posts.
     */
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', Post::class);

        $published = Post::query()->ownedBy($request->user())->published();

        return Inertia::render('Dashboard', [
            'totalViews' => (int) (clone $published)->sum('views_count'),
            'mostRead' => MostReadPostResource::collection(
                $published
                    ->where('views_count', '>', 0)
                    ->orderByDesc('views_count')
                    ->limit(self::MOST_READ_LIMIT)
                    ->get(),
            ),
        ]);
    }
}
