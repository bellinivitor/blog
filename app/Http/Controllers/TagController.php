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
     * List tags with how many posts use each, filtered by name or showing
     * only trashed ones. Tags are created and edited in a dialog on this page.
     */
    public function index(SearchTagRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        $searchDTO = TagSearchDTO::fromRequest($request);

        $tags = Tag::query()
            ->search($searchDTO)
            ->withCount('posts')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('tags/Index', [
            'tags' => TagResource::collection($tags),
            'filters' => $searchDTO->toArray(),
            'counts' => [
                'active' => Tag::query()->count(),
                'trashed' => Tag::query()->onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Store a new tag.
     */
    public function store(StoreTagRequest $request, StoreTagAction $action): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        $action(TagDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag created.']);

        return back(fallback: route('tags.index'));
    }

    /**
     * Update a tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag, UpdateTagAction $action): RedirectResponse
    {
        Gate::authorize('update', $tag);

        $action($tag, TagDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag updated.']);

        return back(fallback: route('tags.index'));
    }

    /**
     * Move a tag to the trash.
     */
    public function destroy(Tag $tag, DeleteTagAction $action): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $action($tag);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag deleted.']);

        return back(fallback: route('tags.index'));
    }

    /**
     * Restore a trashed tag.
     */
    public function restore(Tag $tag, RestoreTagAction $action): RedirectResponse
    {
        Gate::authorize('restore', $tag);

        $action($tag);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag restored.']);

        return back(fallback: route('tags.index'));
    }
}
