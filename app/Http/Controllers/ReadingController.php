<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reading\SearchReadingRequest;
use App\Http\Requests\Reading\StoreReadingRequest;
use App\Http\Requests\Reading\UpdateReadingRequest;
use App\Models\Reading\Reading;
use Domain\Reading\Actions\DeleteReadingAction;
use Domain\Reading\Actions\RestoreReadingAction;
use Domain\Reading\Actions\StoreReadingAction;
use Domain\Reading\Actions\UpdateReadingAction;
use Domain\Reading\DataTransferObjects\ReadingDTO;
use Domain\Reading\DataTransferObjects\ReadingSearchDTO;
use Domain\Reading\Resources\ReadingResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReadingController extends Controller
{
    /**
     * List readings, newest first, optionally filtered by title or showing only trashed ones.
     */
    public function index(SearchReadingRequest $request): Response
    {
        Gate::authorize('viewAny', Reading::class);

        $searchDTO = ReadingSearchDTO::fromRequest($request);

        $readings = Reading::query()
            ->search($searchDTO)
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('readings/Index', [
            'readings' => ReadingResource::collection($readings),
            'filters' => $searchDTO->toArray(),
        ]);
    }

    /**
     * Show the form to create a reading.
     */
    public function create(): Response
    {
        Gate::authorize('create', Reading::class);

        return Inertia::render('readings/Create');
    }

    /**
     * Store a new tag.
     */
    public function store(StoreReadingRequest $request, StoreReadingAction $action): RedirectResponse
    {
        Gate::authorize('create', Reading::class);

        $action(ReadingDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading created.']);

        return to_route('readings.index');
    }

    /**
     * Show the form to edit a reading.
     */
    public function edit(Reading $reading): Response
    {
        Gate::authorize('update', $reading);

        return Inertia::render('readings/Edit', [
            'reading' => ReadingResource::make($reading),
        ]);
    }

    /**
     * Update a reading.
     */
    public function update(UpdateReadingRequest $request, Reading $reading, UpdateReadingAction $action): RedirectResponse
    {
        Gate::authorize('update', $reading);

        $action($reading, ReadingDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading updated.']);

        return to_route('readings.index');
    }

    /**
     * Move a reading to the trash.
     */
    public function destroy(Reading $reading, DeleteReadingAction $action): RedirectResponse
    {
        Gate::authorize('delete', $reading);

        $action($reading);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading deleted.']);

        return to_route('readings.index');
    }

    /**
     * Restore a trashed reading.
     */
    public function restore(Reading $reading, RestoreReadingAction $action): RedirectResponse
    {
        Gate::authorize('restore', $reading);

        $action($reading);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reading restored.']);

        return to_route('readings.index');
    }
}
