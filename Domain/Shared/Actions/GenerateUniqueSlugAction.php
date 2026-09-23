<?php

namespace Domain\Shared\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

readonly class GenerateUniqueSlugAction
{
    /**
     * Build a slug from the given source that is unique in the model's table,
     * including soft deleted rows, by appending an incremental suffix.
     *
     * Slugs listed in $reserved (e.g. clashing with fixed routes) count as taken.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $reserved
     */
    public function __invoke(string $source, string $modelClass, ?int $ignoreId = null, array $reserved = []): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, $reserved, true) || $this->slugExists($slug, $modelClass, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function slugExists(string $slug, string $modelClass, ?int $ignoreId): bool
    {
        return $modelClass::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
