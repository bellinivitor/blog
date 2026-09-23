<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
use Illuminate\Support\Str;

readonly class BuildPostPageMetaAction
{
    private const int DESCRIPTION_LENGTH = 160;

    public function __construct(
        private ExtractPostPlainTextAction $extractPlainText,
    ) {}

    /**
     * Meta for a published post: its excerpt as description, or the start of
     * its text when there is no excerpt.
     */
    public function __invoke(Post $post): PageMetaDTO
    {
        $description = $post->excerpt
            ?: Str::limit(($this->extractPlainText)($post->content), self::DESCRIPTION_LENGTH, '…', preserveWords: true);

        return new PageMetaDTO(
            title: $post->title,
            description: $description,
            url: route('blog.posts.show', $post->slug),
            type: 'article',
            publishedAt: $post->published_at,
            modifiedAt: $post->updated_at,
            tags: $post->tags->map(fn (Tag $tag): string => $tag->name)->all(),
        );
    }
}
