<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\QueryBuilders\PostQueryBuilder;
use Illuminate\Database\Eloquent\Collection;

readonly class FindRelatedPostsAction
{
    /**
     * Posts to suggest after the given one. Each shared tag weighs 1 / (number
     * of published posts with that tag), so a rare tag says more than one used
     * everywhere; ties go to the newest post. When fewer than $limit posts
     * share tags, the latest published posts fill the remaining slots.
     *
     * @return Collection<int, Post>
     */
    public function __invoke(Post $post, int $limit): Collection
    {
        $weights = Tag::query()
            ->whereKey($post->tags->modelKeys())
            ->withCount(['posts' => fn (PostQueryBuilder $query) => $query->published()])
            ->get()
            ->mapWithKeys(fn (Tag $tag): array => [$tag->id => 1 / max(1, $tag->posts_count)]);

        $related = Post::query()
            ->sharingTagsWith($post)
            ->with('tags')
            ->get()
            ->sortBy([
                fn (Post $a, Post $b): int => $this->score($b, $weights->all()) <=> $this->score($a, $weights->all()),
                fn (Post $a, Post $b): int => $b->published_at <=> $a->published_at,
            ])
            ->take($limit)
            ->values();

        if ($related->count() < $limit) {
            $related = $related->concat(
                Post::query()
                    ->published()
                    ->whereKeyNot([$post->id, ...$related->modelKeys()])
                    ->with('tags')
                    ->orderByDesc('published_at')
                    ->limit($limit - $related->count())
                    ->get(),
            );
        }

        return $related;
    }

    /**
     * @param  array<int, float>  $weights
     */
    private function score(Post $candidate, array $weights): float
    {
        return $candidate->tags->sum(fn (Tag $tag): float => $weights[$tag->id] ?? 0.0);
    }
}
