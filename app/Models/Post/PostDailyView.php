<?php

namespace App\Models\Post;

use App\Models\DefaultModel;
use Carbon\CarbonImmutable;
use Database\Factories\Post\PostDailyViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reader views of a post on one day (in the blog timezone). Only a count:
 * nothing about the reader is stored.
 *
 * @property int $id
 * @property int $post_id
 * @property CarbonImmutable $date
 * @property int $views
 * @property-read Post $post
 */
#[Fillable(['post_id', 'date', 'views'])]
#[UseFactory(PostDailyViewFactory::class)]
class PostDailyView extends DefaultModel
{
    public $timestamps = false;

    /**
     * Store the day alone, matching the rows written by the view counter.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
