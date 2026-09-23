<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\PublishPostRequest;
use App\Http\Requests\Post\SearchPostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\Actions\ChangePostStatusAction;
use Domain\Post\Actions\DeletePostAction;
use Domain\Post\Actions\RestorePostAction;
use Domain\Post\Actions\StorePostAction;
use Domain\Post\Actions\UpdatePostAction;
use Domain\Post\DataTransferObjects\PostDTO;
use Domain\Post\DataTransferObjects\PostSearchDTO;
use Domain\Post\Resources\PostResource;
use Domain\Post\States\DraftState;
use Domain\Post\States\PublishedState;
use Domain\Tag\Resources\TagResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * List the user's posts, filtered by title, status, tag or trash.
     */
    public function index(SearchPostRequest $request): Response
    {
        Gate::authorize('viewAny', Post::class);

        $searchDTO = PostSearchDTO::fromRequest($request);

        $posts = Post::query()
            ->ownedBy($request->user())
            ->search($searchDTO)
            ->with('tags')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('posts/Index', [
            'posts' => PostResource::collection($posts),
            'filters' => $searchDTO->toArray(),
            'tags' => $this->availableTags(),
        ]);
    }

    /**
     * Show the form to create a post.
     */
    public function create(): Response
    {
        Gate::authorize('create', Post::class);

        return Inertia::render('posts/Create', [
            'tags' => $this->availableTags(),
        ]);
    }

    /**
     * Store a new draft post.
     */
    public function store(StorePostRequest $request, StorePostAction $action): RedirectResponse
    {
        Gate::authorize('create', Post::class);

        $post = $action(PostDTO::fromRequest($request), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post created.']);

        return to_route('posts.edit', $post);
    }

    /**
     * Show the form to edit a post.
     */
    public function edit(Post $post): Response
    {
        Gate::authorize('update', $post);

        return Inertia::render('posts/Edit', [
            'post' => PostResource::make($post->load('tags')),
            'tags' => $this->availableTags(),
        ]);
    }

    /**
     * Update a post.
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action($post, PostDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post updated.']);

        return to_route('posts.edit', $post);
    }

    /**
     * Move a post to the trash.
     */
    public function destroy(Post $post, DeletePostAction $action): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $action($post);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post deleted.']);

        return to_route('posts.index');
    }

    /**
     * Restore a trashed post.
     */
    public function restore(Post $post, RestorePostAction $action): RedirectResponse
    {
        Gate::authorize('restore', $post);

        $action($post);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post restored.']);

        return to_route('posts.index');
    }

    /**
     * Publish a draft post now, or at the given date (a future date schedules it).
     */
    public function publish(PublishPostRequest $request, Post $post, ChangePostStatusAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post = $action($post, new PublishedState($post->status), $request->date('published_at'));

        $message = $post->published_at?->isFuture() ? 'Post scheduled.' : 'Post published.';

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }

    /**
     * Move a published post back to draft.
     */
    public function unpublish(Post $post, ChangePostStatusAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action($post, new DraftState($post->status));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post moved back to draft.']);

        return back();
    }

    private function availableTags(): AnonymousResourceCollection
    {
        return TagResource::collection(Tag::query()->orderBy('name')->get());
    }
}
