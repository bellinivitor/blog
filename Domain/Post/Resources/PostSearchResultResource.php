<?php

namespace Domain\Post\Resources;

use App\Models\Post\Post;
use Domain\Post\Actions\ExtractPostPlainTextAction;
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
        $text = app(ExtractPostPlainTextAction::class)($this->content);
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
}
