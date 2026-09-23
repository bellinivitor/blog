<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\SearchBlogRequest;
use App\Models\Post\Post;
use Domain\Post\DataTransferObjects\BlogSearchDTO;
use Domain\Post\Resources\PostSearchResultResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogSearchController extends Controller
{
    private const int MAX_RESULTS = 8;

    /**
     * Search published posts by title or content.
     */
    public function index(SearchBlogRequest $request): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->published()
            ->matchingText(BlogSearchDTO::fromRequest($request))
            ->orderByDesc('published_at')
            ->limit(self::MAX_RESULTS)
            ->get();

        return PostSearchResultResource::collection($posts);
    }
}
