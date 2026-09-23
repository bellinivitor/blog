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
     * Point each reading link of a parsed post at the reading's current URL,
     * opening in a new tab. A link to a missing or trashed reading keeps only
     * its text, so readers never meet a broken link.
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

        $urls = Reading::query()
            ->whereKey(array_map($this->readingId(...), $links))
            ->pluck('url', 'id');

        foreach ($links as $link) {
            $url = $urls->get($this->readingId($link));

            if ($url === null) {
                $this->unwrap($link);

                continue;
            }

            $link->setUrl($url);
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
