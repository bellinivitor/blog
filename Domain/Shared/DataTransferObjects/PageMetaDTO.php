<?php

namespace Domain\Shared\DataTransferObjects;

use Carbon\CarbonInterface;

/**
 * Title, description and Open Graph data for the <head> of a public page.
 * Built on the server, not from a request, so it does not implement
 * DataTransferObjectInterface.
 */
final readonly class PageMetaDTO
{
    /**
     * @param  'website'|'article'  $type
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $url,
        public string $type = 'website',
        public ?CarbonInterface $publishedAt = null,
        public ?CarbonInterface $modifiedAt = null,
        public array $tags = [],
    ) {}
}
