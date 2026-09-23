<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
use Inertia\Inertia;
use Inertia\Response;

class BlogPrivacyController extends Controller
{
    /**
     * What the blog stores on the reader's device and why (LGPD transparency).
     */
    public function show(): Response
    {
        return Inertia::render('blog/Privacy', [
            'sessionCookie' => config('session.cookie'),
        ])->withViewData(['meta' => new PageMetaDTO(
            title: 'Privacidade e cookies',
            description: 'Quais cookies este blog usa, para quê, e o que não é coletado.',
            url: route('blog.privacy'),
        )]);
    }
}
