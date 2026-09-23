<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ask search engines not to index anything outside the public blog (admin
 * panel, login, settings), even when those pages are reached through a link.
 */
class KeepPrivatePagesOutOfSearch
{
    /**
     * Route names that belong to the public blog and may be indexed.
     *
     * @var array<int, string>
     */
    private const array PUBLIC_ROUTES = ['home', 'blog.*', 'sitemap', 'robots'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->routeIs(...self::PUBLIC_ROUTES)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
