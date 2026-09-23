<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\KeepPrivatePagesOutOfSearch;
use Domain\Post\Actions\FindReadingSuggestionsAction;
use Domain\Post\Resources\PublishedPostResource;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Behind Cloudflare, read the visitor's IP and scheme from the forwarded
        // headers, but only when the request really comes from Cloudflare: the
        // origin is reachable directly, so anyone else could forge them.
        // Ranges from https://www.cloudflare.com/ips/
        $middleware->trustProxies(at: [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ]);

        // First in the group so it also marks redirects issued by `auth`.
        $middleware->web(prepend: [
            KeepPrivatePagesOutOfSearch::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Errors in the blog's own look. With APP_DEBUG a 500 keeps Laravel's
        // detailed page; the others are just as helpful in the blog layout.
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            $status = $response->statusCode();

            if ($response->request->expectsJson()) {
                return null;
            }

            // Expired CSRF token: send the author back to the form to retry.
            if ($status === 419) {
                Inertia::flash('toast', ['type' => 'error', 'message' => 'A página expirou. Tente de novo.']);

                return back();
            }

            if (! in_array($status, [403, 404, 500, 503], true) || ($status === 500 && config('app.debug'))) {
                return null;
            }

            // A lost reader gets something to read; a failing database must
            // not break the error page itself.
            $suggestions = $status === 404
                ? rescue(fn () => PublishedPostResource::collection(app(FindReadingSuggestionsAction::class)(2)), [], report: false)
                : [];

            return $response
                ->render('blog/Error', ['status' => $status, 'suggestions' => $suggestions])
                ->withSharedData();
        });
    })->create();
