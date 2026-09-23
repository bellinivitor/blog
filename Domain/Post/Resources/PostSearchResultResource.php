<?php

namespace Domain\Post\Resources;

use App\Models\Post\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * A published post found by the blog search, with a plain-text snippet of
 * the content around the searched term.
 *
 * @mixin Post
 */
class PostSearchResultResource extends JsonResource
{
    private const int CONTEXT_BEFORE = 60;

    private const int SNIPPET_LENGTH = 180;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'published_at' => $this->published_at?->toIso8601String(),
            'snippet' => $this->snippet((string) $request->query('q', '')),
        ];
    }

    private function snippet(string $term): string
    {
        $text = $this->plainText();
        $position = $term === '' ? false : mb_stripos($text, trim($term));

        if ($position === false) {
            return $this->excerpt ?: Str::limit($text, self::SNIPPET_LENGTH);
        }

        $start = max(0, $position - self::CONTEXT_BEFORE);
        $snippet = mb_substr($text, $start, self::SNIPPET_LENGTH);

        return ($start > 0 ? '…' : '')
            .trim($snippet)
            .($start + self::SNIPPET_LENGTH < mb_strlen($text) ? '…' : '');
    }

    /**
     * The Markdown content without its syntax, on a single line.
     */
    private function plainText(): string
    {
        $text = preg_replace([
            '/```[^\n]*\n?/',
            '/!\[([^\]]*)\]\([^)]*\)/',
            '/\[([^\]]*)\]\([^)]*\)/',
            '/^\s{0,3}(#{1,6}|>|[-*+]|\d+\.)\s+/m',
            '/[*_`~]/',
        ], ['', '$1', '$1', '', ''], $this->content) ?? $this->content;

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
