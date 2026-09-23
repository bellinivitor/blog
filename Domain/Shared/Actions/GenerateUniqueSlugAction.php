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
     * @param  class-string<Model>  $modelClass
     */
    public function __invoke(string $source, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $modelClass, $ignoreId)) {
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
