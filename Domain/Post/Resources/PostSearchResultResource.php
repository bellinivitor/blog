<?php

namespace Domain\Post\Resources;

use App\Models\Post\Post;
use Domain\Post\Actions\ExtractPostPlainTextAction;
use Domain\Post\Actions\NormalizeSearchTextAction;
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
        $position = $term === '' ? null : $this->locate($text, trim($term));

        if ($position === null) {
            return $this->excerpt ?: Str::limit($text, self::SNIPPET_LENGTH);
        }

        $start = max(0, $position - self::CONTEXT_BEFORE);
        $snippet = mb_substr($text, $start, self::SNIPPET_LENGTH);

        return ($start > 0 ? '…' : '')
            .trim($snippet)
            .($start + self::SNIPPET_LENGTH < mb_strlen($text) ? '…' : '');
    }

    /**
     * Position of the term in the text, ignoring accents and case. Each
     * character is normalized on its own so offsets map back to the original.
     */
    private function locate(string $text, string $term): ?int
    {
        $normalize = app(NormalizeSearchTextAction::class);
        $normalized = '';
        $originalIndex = [];

        foreach (mb_str_split($text) as $index => $character) {
            $piece = $normalize($character);
            $normalized .= $piece;
            array_push($originalIndex, ...array_fill(0, mb_strlen($piece), $index));
        }

        $position = mb_strpos($normalized, $normalize($term));

        return $position === false ? null : $originalIndex[$position];
    }
}
