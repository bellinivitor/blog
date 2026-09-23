<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\Resources\PublishedPostResource;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
use Domain\Tag\Resources\TagResource;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class BlogTagController extends Controller
{
    /**
     * Published posts filed under a tag.
     */
    public function show(Tag $tag): Response
    {
        $posts = Post::query()
            ->published()
            ->whereHas('tags', fn (Builder $query) => $query->whereKey($tag->id))
            ->with('tags')
            ->orderByDesc('published_at')
            ->get();

        return Inertia::render('blog/Tag', [
            'tag' => TagResource::make($tag),
            'posts' => PublishedPostResource::collection($posts),
        ])->withViewData(['meta' => new PageMetaDTO(
            title: $tag->name,
            description: 'Posts de '.config('blog.author')." sobre {$tag->name}.",
            url: route('blog.tags.show', $tag->slug),
        )]);
    }
}
