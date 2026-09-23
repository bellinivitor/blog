<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

readonly class RenderPostContentAction
{
    /**
     * Render the post's Markdown to HTML with syntax highlighted code blocks
     * (light and dark themes). Raw HTML in the Markdown is escaped. The result
     * is cached per post version.
     */
    public function __invoke(Post $post): string
    {
        $version = $post->updated_at?->getTimestamp() ?? 0;

        return Cache::rememberForever(
            "posts.{$post->id}.html.{$version}",
            fn (): string => $this->converter()->convert($post->content)->getContent(),
        );
    }

    private function converter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new PhikiExtension([
            'light' => Theme::GithubLight,
            'dark' => Theme::GithubDark,
        ]));

        return new MarkdownConverter($environment);
    }
}
