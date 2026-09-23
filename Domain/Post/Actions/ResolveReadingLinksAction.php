<?php

namespace Domain\Post\Actions;

use App\Models\Reading\Reading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document;

readonly class ResolveReadingLinksAction
{
    /**
     * Scheme of a reading cited in a post: [text](leitura:ID).
     */
    public const string SCHEME = 'leitura:';

    /**
     * Anchor of a reading on the public readings page.
     */
    public static function anchor(int $readingId): string
    {
        return "leitura-{$readingId}";
    }

    /**
     * Point each reading link of a parsed post at that reading on the blog's
     * readings page, opening in a new tab. A link to a missing or trashed
     * reading keeps only its text, so readers never meet a broken link.
     */
    public function __invoke(Document $document): void
    {
        $links = [];

        foreach ($document->iterator() as $node) {
            if ($node instanceof Link && str_starts_with($node->getUrl(), self::SCHEME)) {
                $links[] = $node;
            }
        }

        if ($links === []) {
            return;
        }

        $existingIds = Reading::query()
            ->whereKey(array_map($this->readingId(...), $links))
            ->pluck('id')
            ->all();
        $readingsPage = route('blog.readings.index');

        foreach ($links as $link) {
            $readingId = $this->readingId($link);

            if (! in_array($readingId, $existingIds, true)) {
                $this->unwrap($link);

                continue;
            }

            $link->setUrl($readingsPage.'#'.self::anchor($readingId));
            $link->data->set('attributes/target', '_blank');
            $link->data->set('attributes/rel', 'noopener');
        }
    }

    private function readingId(Link $link): int
    {
        return (int) substr($link->getUrl(), strlen(self::SCHEME));
    }

    private function unwrap(Link $link): void
    {
        foreach ($link->children() as $child) {
            $link->insertBefore($child);
        }

        $link->detach();
    }
}
