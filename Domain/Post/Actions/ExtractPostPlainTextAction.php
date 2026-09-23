<?php

namespace Domain\Post\Actions;

readonly class ExtractPostPlainTextAction
{
    /**
     * The post Markdown without its syntax, collapsed to a single line.
     */
    public function __invoke(string $markdown): string
    {
        $text = preg_replace([
            '/```[^\n]*\n?/',
            '/!\[([^\]]*)\]\([^)]*\)/',
            '/\[([^\]]*)\]\([^)]*\)/',
            '/^\s{0,3}(#{1,6}|>|[-*+]|\d+\.)\s+/m',
            '/[*_`~]/',
        ], ['', '$1', '$1', '', ''], $markdown) ?? $markdown;

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
