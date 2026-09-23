<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Reading\Reading;
use Domain\Reading\Resources\ReadingResource;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
use Inertia\Inertia;
use Inertia\Response;

class BlogReadingController extends Controller
{
    /**
     * Every recommended reading, newest first.
     */
    public function index(): Response
    {
        return Inertia::render('blog/Readings', [
            'readings' => ReadingResource::collection(Reading::query()->latestFirst()->get()),
        ])->withViewData(['meta' => new PageMetaDTO(
            title: 'Leituras',
            description: 'Livros e artigos que '.config('blog.author').' recomenda.',
            url: route('blog.readings.index'),
        )]);
    }
}
