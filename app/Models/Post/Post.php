<?php

namespace App\Models\Post;

use App\Models\DefaultModel;
use App\Models\Tag\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\Post\PostFactory;
use Domain\Post\Enums\PostStatus;
use Domain\Post\Policies\PostPolicy;
use Domain\Post\QueryBuilders\PostQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property int $author_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content
 * @property PostStatus $status
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User $author
 * @property-read Collection<int, Tag> $tags
 *
 * @method static PostQueryBuilder query()
 */
#[Fillable(['title', 'slug', 'excerpt', 'content'])]
#[UseFactory(PostFactory::class)]
#[UsePolicy(PostPolicy::class)]
class Post extends DefaultModel
{
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'immutable_datetime',
        ];
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): PostQueryBuilder
    {
        return new PostQueryBuilder($query);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
