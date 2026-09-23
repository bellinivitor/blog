<?php

namespace Domain\Post\Actions;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

readonly class ListReservedSlugsAction
{
    /**
     * Paths served by the web server itself rather than by a route.
     *
     * @var array<int, string>
     */
    private const array STATIC_PATHS = ['build', 'storage'];

    public function __construct(
        private Router $router,
    ) {}

    /**
     * Slugs a post cannot use because posts live at /{slug}: the first path
     * segment of every fixed route (admin, feed, search, tags...).
     *
     * @return array<int, string>
     */
    public function __invoke(): array
    {
        $segments = collect($this->router->getRoutes()->getRoutes())
            ->map(fn (Route $route): string => explode('/', $route->uri())[0])
            ->reject(fn (string $segment): bool => $segment === '' || str_starts_with($segment, '{'));

        return $segments
            ->merge(self::STATIC_PATHS)
            ->unique()
            ->values()
            ->all();
    }
}
