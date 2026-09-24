<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Keep bots from inflating likes: one like per post per visitor a day,
     * and a small hourly budget across all posts. The visitor is an HMAC of
     * the IP keyed on the app key, so the limiter never holds the raw IP.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('post-likes', function (Request $request): array {
            $visitor = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
            $slug = (string) $request->route('slug');

            return [
                Limit::perDay(1)->by("post-likes:{$slug}:{$visitor}"),
                Limit::perHour(20)->by("post-likes:{$visitor}"),
            ];
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        JsonResource::withoutWrapping();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
