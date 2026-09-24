<?php

namespace Domain\Reading\Actions;

use App\Models\Post\Post;
use App\Models\Reading\Reading;
use Domain\Post\Actions\ResolveReadingLinksAction;
use Illuminate\Support\Collection;

readonly class CountReadingCitationsAction
{
    /**
     * Set citations_count on each reading: how many posts (not trashed) link
     * to it as [text](leitura:ID). A post citing a reading twice counts once.
     *
     * @param  Collection<int, Reading>  $readings
     */
    public function __invoke(Collection $readings): void
    {
        if ($readings->isEmpty()) {
            return;
        }

        $scheme = ResolveReadingLinksAction::SCHEME;
        $citations = [];

        Post::query()
            ->where('content', 'like', '%]('.$scheme.'%')
            ->select(['id', 'content'])
            ->each(function (Post $post) use ($scheme, &$citations): void {
                preg_match_all('/\]\('.preg_quote($scheme, '/').'(\d+)\)/', $post->content, $matches);

                foreach (array_unique($matches[1]) as $readingId) {
                    $citations[(int) $readingId] = ($citations[(int) $readingId] ?? 0) + 1;
                }
            });

        $readings->each(fn (Reading $reading) => $reading->setAttribute('citations_count', $citations[$reading->id] ?? 0));
    }
}
