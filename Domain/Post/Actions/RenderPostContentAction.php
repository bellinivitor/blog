<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

readonly class RenderPostContentAction
{
    /**
     * Bump whenever the rendering pipeline changes, so cached HTML is rebuilt.
     */
    private const int RENDERER_VERSION = 2;

    /**
     * Render the post's Markdown to HTML with syntax highlighted code blocks
     * (light and dark themes) and ids on h2/h3 headings for the table of
     * contents. Raw HTML in the Markdown is escaped. The result is cached per
     * post version.
     */
    public function __invoke(Post $post): string
    {
        $version = $post->updated_at?->getTimestamp() ?? 0;

        return Cache::rememberForever(
            "posts.{$post->id}.html.v".self::RENDERER_VERSION.".{$version}",
            fn (): string => $this->converter()->convert($post->content)->getContent(),
        );
    }

    private function converter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'insert' => 'none',
                'apply_id_to_heading' => true,
                'id_prefix' => '',
                'fragment_prefix' => '',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addExtension(new PhikiExtension([
            'light' => Theme::GithubLight,
            'dark' => Theme::GithubDark,
        ]));

        return new MarkdownConverter($environment);
    }
}
