<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reading\SearchReadingRequest;
use App\Http\Requests\Reading\StoreReadingRequest;
use App\Http\Requests\Reading\UpdateReadingRequest;
use App\Models\Reading\Reading;
use Domain\Reading\Actions\CountReadingCitationsAction;
use Domain\Reading\Actions\DeleteReadingAction;
use Domain\Reading\Actions\RestoreReadingAction;
use Domain\Reading\Actions\StoreReadingAction;
use Domain\Reading\Actions\UpdateReadingAction;
use Domain\Reading\DataTransferObjects\ReadingDTO;
use Domain\Reading\DataTransferObjects\ReadingSearchDTO;
use Domain\Reading\Resources\ReadingResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReadingController extends Controller
{
    private const int MAX_SEARCH_RESULTS = 8;

    /**
     * List readings, newest first, with how many posts cite each, filtered by
     * title or showing only trashed ones. Readings are created and edited in a
     * dialog on this page.
     */
    public function index(SearchReadingRequest $request, CountReadingCitationsAction $countCitations): Response
    {
        Gate::authorize('viewAny', Reading::class);

        $searchDTO = ReadingSearchDTO::fromRequest($request);

        $readings = Reading::query()
            ->search($searchDTO)
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        $countCitations($readings->getCollection());

        return Inertia::render('readings/Index', [
            'readings' => ReadingResource::collection($readings),
            'filters' => $searchDTO->toArray(),
            'counts' => [
                'active' => Reading::query()->count(),
                'trashed' => Reading::query()->onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Readings matching a title, for the "insert reading" picker of the post
     * editor (JSON).
     */
    public function search(SearchReadingRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Reading::class);

        $readings = Reading::query()
            ->search(ReadingSearchDTO::fromRequest($request))
            ->latestFirst()
            ->limit(self::MAX_SEARCH_RESULTS)
            ->get();

        return ReadingResource::collection($readings);
    }

    /**
     * Store a new reading.
     */
    public function store(StoreReadingRequest $request, StoreReadingAction $action): RedirectResponse
    {
        Gate::authorize('create', Reading::class);

        $action(ReadingDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading created.']);

        return back(fallback: route('readings.index'));
    }

    /**
     * Update a reading.
     */
    public function update(UpdateReadingRequest $request, Reading $reading, UpdateReadingAction $action): RedirectResponse
    {
        Gate::authorize('update', $reading);

        $action($reading, ReadingDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading updated.']);

        return back(fallback: route('readings.index'));
    }

    /**
     * Move a reading to the trash.
     */
    public function destroy(Reading $reading, DeleteReadingAction $action): RedirectResponse
    {
        Gate::authorize('delete', $reading);

        $action($reading);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading deleted.']);

        return back(fallback: route('readings.index'));
    }

    /**
     * Restore a trashed reading.
     */
    public function restore(Reading $reading, RestoreReadingAction $action): RedirectResponse
    {
        Gate::authorize('restore', $reading);

        $action($reading);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading restored.']);

        return back(fallback: route('readings.index'));
    }
}
