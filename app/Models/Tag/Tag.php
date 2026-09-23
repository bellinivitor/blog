<?php

namespace App\Models\Tag;

use App\Models\DefaultModel;
use App\Models\Post\Post;
use Carbon\CarbonImmutable;
use Database\Factories\Tag\TagFactory;
use Domain\Tag\Policies\TagPolicy;
use Domain\Tag\QueryBuilders\TagQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property CarbonImmutable|null $deleted_at
 * @property-read int|null $posts_count
 *
 * @method static TagQueryBuilder query()
 */
#[Fillable(['name', 'slug'])]
#[UseFactory(TagFactory::class)]
#[UsePolicy(TagPolicy::class)]
class Tag extends DefaultModel
{
    use SoftDeletes;

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): TagQueryBuilder
    {
        return new TagQueryBuilder($query);
    }

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
