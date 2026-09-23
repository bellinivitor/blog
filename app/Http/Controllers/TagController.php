<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\SearchTagRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag\Tag;
use Domain\Tag\Actions\DeleteTagAction;
use Domain\Tag\Actions\RestoreTagAction;
use Domain\Tag\Actions\StoreTagAction;
use Domain\Tag\Actions\UpdateTagAction;
use Domain\Tag\DataTransferObjects\TagDTO;
use Domain\Tag\DataTransferObjects\TagSearchDTO;
use Domain\Tag\Resources\TagResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * List tags, optionally filtered by name or showing only trashed ones.
     */
    public function index(SearchTagRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        $searchDTO = TagSearchDTO::fromRequest($request);

        $tags = Tag::query()
            ->search($searchDTO)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('tags/Index', [
            'tags' => TagResource::collection($tags),
            'filters' => $searchDTO->toArray(),
        ]);
    }

    /**
     * Show the form to create a tag.
     */
    public function create(): Response
    {
        Gate::authorize('create', Tag::class);

        return Inertia::render('tags/Create');
    }

    /**
     * Store a new tag.
     */
    public function store(StoreTagRequest $request, StoreTagAction $action): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        $action(TagDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag created.']);

        return to_route('tags.index');
    }

    /**
     * Show the form to edit a tag.
     */
    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('tags/Edit', [
            'tag' => TagResource::make($tag),
        ]);
    }

    /**
     * Update a tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag, UpdateTagAction $action): RedirectResponse
    {
        Gate::authorize('update', $tag);

        $action($tag, TagDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag updated.']);

        return to_route('tags.index');
    }

    /**
     * Move a tag to the trash.
     */
    public function destroy(Tag $tag, DeleteTagAction $action): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $action($tag);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag deleted.']);

        return to_route('tags.index');
    }

    /**
     * Restore a trashed tag.
     */
    public function restore(Tag $tag, RestoreTagAction $action): RedirectResponse
    {
        Gate::authorize('restore', $tag);

        $action($tag);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag restored.']);

        return to_route('tags.index');
    }
}
