<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use App\Models\Reading\Reading;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\Xml;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

readonly class RenderPostContentAction
{
    /**
     * Bump whenever the rendering pipeline changes, so cached HTML is rebuilt.
     */
    private const int RENDERER_VERSION = 5;

    public function __construct(
        private ResolveReadingLinksAction $resolveReadingLinks,
    ) {}

    /**
     * Render the post's Markdown to HTML with syntax highlighted code blocks
     * (light and dark themes) and ids on h2/h3 headings for the table of
     * contents. Mermaid blocks keep their source, escaped, for the page to draw
     * as diagrams. Raw HTML in the Markdown is escaped. Cited readings
     * ([text](leitura:ID)) link to their current URL. The result is cached
     * per post version and, when the post cites readings, per readings version.
     */
    public function __invoke(Post $post): string
    {
        $version = $post->updated_at?->getTimestamp() ?? 0;

        if (str_contains($post->content, ']('.ResolveReadingLinksAction::SCHEME)) {
            $version .= '.'.$this->readingsVersion();
        }

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

        $environment->addEventListener(
            DocumentParsedEvent::class,
            function (DocumentParsedEvent $event): void {
                ($this->resolveReadingLinks)($event->getDocument());
                $this->lowercaseCodeLanguages($event->getDocument());
            },
        );

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addExtension(new PhikiExtension([
            'light' => Theme::GithubLight,
            'dark' => Theme::GithubDark,
        ]));
        $environment->addRenderer(FencedCode::class, $this->mermaidRenderer(), 20);

        return new MarkdownConverter($environment);
    }

    /**
     * The editor writes a block's language as its display name (```PHP,
     * ```TypeScript), but Phiki only knows lowercase names.
     */
    private function lowercaseCodeLanguages(Document $document): void
    {
        foreach ($document->iterator() as $node) {
            if ($node instanceof FencedCode && $node->getInfo() !== null) {
                $node->setInfo(mb_strtolower($node->getInfo()));
            }
        }
    }

    /**
     * Renders ```mermaid blocks as <pre class="mermaid"> with the escaped
     * source, ahead of Phiki; other blocks fall through to it.
     */
    private function mermaidRenderer(): NodeRendererInterface
    {
        return new class implements NodeRendererInterface
        {
            public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?string
            {
                if (! $node instanceof FencedCode || ($node->getInfoWords()[0] ?? '') !== 'mermaid') {
                    return null;
                }

                return '<pre class="mermaid">'.Xml::escape($node->getLiteral()).'</pre>';
            }
        };
    }

    /**
     * Changes whenever a reading is created, edited, trashed or restored
     * (soft deletes touch updated_at too).
     */
    private function readingsVersion(): string
    {
        $latest = Reading::query()->withTrashed()->max('updated_at');

        return $latest === null ? '0' : (string) strtotime((string) $latest);
    }
}
