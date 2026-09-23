<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostImageRequest;
use App\Models\Post\Post;
use Domain\Post\Actions\StorePostImageAction;
use Domain\Post\DataTransferObjects\PostImageDTO;
use Domain\Post\Resources\PostImageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PostImageController extends Controller
{
    /**
     * Upload an image to be embedded in a post's content.
     */
    public function store(StorePostImageRequest $request, StorePostImageAction $action): JsonResponse
    {
        Gate::authorize('create', Post::class);

        return PostImageResource::make($action(PostImageDTO::fromRequest($request)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
