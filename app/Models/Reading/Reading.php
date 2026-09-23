<?php

namespace App\Models\Reading;

use App\Models\DefaultModel;
use Carbon\CarbonImmutable;
use Database\Factories\Reading\ReadingFactory;
use Domain\Reading\Policies\ReadingPolicy;
use Domain\Reading\QueryBuilders\ReadingQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * A recommended reading: a book to buy, an article... in the end, a link.
 *
 * @property int $id
 * @property string $title
 * @property string $url
 * @property CarbonImmutable|null $deleted_at
 *
 * @method static ReadingQueryBuilder query()
 */
#[Fillable(['title', 'url'])]
#[UseFactory(ReadingFactory::class)]
#[UsePolicy(ReadingPolicy::class)]
class Reading extends DefaultModel
{
    use SoftDeletes;

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): ReadingQueryBuilder
    {
        return new ReadingQueryBuilder($query);
    }
}
