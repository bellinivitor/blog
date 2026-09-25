import { onBeforeUnmount, watch } from 'vue';
import type { Ref } from 'vue';
import { renderMermaidBlocks } from '@/lib/mermaid';

const COPIED_FOR_MS = 2000;

/**
 * Progressive enhancements for server-rendered article HTML (v-html):
 * a "#" link on every h2/h3, a copy button on every code block and Mermaid
 * blocks drawn as diagrams (redrawn when the color scheme changes).
 */
/**
 * Copy text with the async Clipboard API, falling back to the legacy
 * execCommand path where the API is missing or blocked by policy.
 */
async function copyText(text: string): Promise<void> {
    try {
        await navigator.clipboard.writeText(text);

        return;
    } catch {
        // Fall through to the legacy path.
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.append(textarea);
    textarea.select();

    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) {
        throw new Error('Copy failed');
    }
}

export function useArticleEnhancements(
    body: Readonly<Ref<HTMLElement | null>>,
): void {
    const timers: ReturnType<typeof setTimeout>[] = [];
    let schemeObserver: MutationObserver | null = null;

    function addHeadingAnchors(root: HTMLElement): void {
        root.querySelectorAll<HTMLElement>('h2[id], h3[id]').forEach(
            (heading) => {
                if (heading.querySelector('.heading-anchor')) {
                    return;
                }

                const title = heading.textContent?.trim() ?? '';
                heading.dataset.title = title;

                const anchor = document.createElement('a');
                anchor.className = 'heading-anchor';
                anchor.href = `#${encodeURIComponent(heading.id)}`;
                anchor.textContent = '#';
                anchor.setAttribute(
                    'aria-label',
                    `Link para a seção “${title}”`,
                );

                heading.append(anchor);
            },
        );
    }

    function addCopyButtons(root: HTMLElement): void {
        root.querySelectorAll<HTMLPreElement>('pre').forEach((pre) => {
            if (
                pre.classList.contains('mermaid') ||
                pre.parentElement?.classList.contains('code-block')
            ) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'code-block';
            pre.replaceWith(wrapper);
            wrapper.append(pre);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'code-copy';
            button.textContent = 'Copiar';
            button.title = 'Copiar código';
            button.setAttribute('aria-live', 'polite');

            button.addEventListener('click', async () => {
                const code = pre.querySelector('code') ?? pre;

                try {
                    await copyText(code.innerText.replace(/\n$/, ''));
                    button.textContent = 'Copiado';
                } catch {
                    button.textContent = 'Não copiou';
                }

                timers.push(
                    setTimeout(() => {
                        button.textContent = 'Copiar';
                    }, COPIED_FOR_MS),
                );
            });

            wrapper.append(button);
        });
    }

    function drawDiagrams(root: HTMLElement): void {
        schemeObserver?.disconnect();
        schemeObserver = null;

        if (!root.querySelector('pre.mermaid')) {
            return;
        }

        void renderMermaidBlocks(root);

        schemeObserver = new MutationObserver(
            () => void renderMermaidBlocks(root),
        );
        schemeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    }

    // The body element changes when the page moves to another post.
    watch(
        body,
        (root) => {
            if (root) {
                addHeadingAnchors(root);
                addCopyButtons(root);
                drawDiagrams(root);
            }
        },
        { immediate: true, flush: 'post' },
    );

    onBeforeUnmount(() => {
        timers.forEach(clearTimeout);
        schemeObserver?.disconnect();
    });
}
