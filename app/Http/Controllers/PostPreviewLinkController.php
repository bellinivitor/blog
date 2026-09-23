<?php

namespace App\Http\Controllers;

use App\Models\Post\Post;
use Domain\Post\Actions\DisablePostPreviewLinkAction;
use Domain\Post\Actions\EnablePostPreviewLinkAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PostPreviewLinkController extends Controller
{
    /**
     * Enable the public preview link, to share the post before it goes live.
     */
    public function store(Post $post, EnablePostPreviewLinkAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action($post);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Preview link enabled.']);

        return back();
    }

    /**
     * Disable the public preview link.
     */
    public function destroy(Post $post, DisablePostPreviewLinkAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action($post);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Preview link disabled.']);

        return back();
    }
}
