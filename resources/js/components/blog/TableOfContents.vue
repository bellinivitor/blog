<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

type Heading = {
    id: string;
    text: string;
    level: 2 | 3;
};

const props = withDefaults(
    defineProps<{
        /** Element holding the rendered article, whose h2/h3 carry ids. */
        source: HTMLElement | null;
        /** `aside`: sticky list in the margin; `inline`: collapsible, for small screens. */
        variant?: 'aside' | 'inline';
    }>(),
    { variant: 'aside' },
);

const details = ref<HTMLDetailsElement | null>(null);

const headings = ref<Heading[]>([]);
const activeIds = ref<Set<string>>(new Set());

/** Pixels of a section that must be on screen for it to count as visible. */
const MIN_VISIBLE = 48;

let article: HTMLElement | null = null;
let elements: HTMLElement[] = [];
let frame = 0;

function collectHeadings(root: HTMLElement): HTMLElement[] {
    return [...root.querySelectorAll<HTMLElement>('h2[id], h3[id]')];
}

/**
 * Mark every section currently on screen. A section runs from its heading
 * to the next one (or the end of the article), so the highlighted items read
 * as one continuous stretch of the rail.
 */
function updateActiveHeadings(): void {
    frame = 0;

    if (!article || elements.length === 0) {
        return;
    }

    const articleBottom = article.getBoundingClientRect().bottom;
    const visible = new Set<string>();

    elements.forEach((el, index) => {
        const top = el.getBoundingClientRect().top;
        const bottom =
            elements[index + 1]?.getBoundingClientRect().top ?? articleBottom;
        const onScreen =
            Math.min(bottom, window.innerHeight) - Math.max(top, 0);

        if (onScreen >= Math.min(MIN_VISIBLE, bottom - top)) {
            visible.add(el.id);
        }
    });

    activeIds.value = visible;
}

function onScroll(): void {
    if (!frame) {
        frame = requestAnimationFrame(updateActiveHeadings);
    }
}

function isFirstActive(id: string): boolean {
    return (
        headings.value.find((heading) => activeIds.value.has(heading.id))
            ?.id === id
    );
}

function scrollTo(event: MouseEvent, id: string): void {
    const target = document.getElementById(id);

    if (!target) {
        return;
    }

    event.preventDefault();

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (details.value) {
        details.value.open = false;
    }

    target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
    history.replaceState(null, '', `#${encodeURIComponent(id)}`);
}

function build(source: HTMLElement): void {
    article = source;
    elements = collectHeadings(source);

    headings.value = elements.map((el) => ({
        id: el.id,
        // The heading may already carry an injected "#" anchor.
        text: el.dataset.title ?? el.textContent?.trim() ?? '',
        level: el.tagName === 'H3' ? 3 : 2,
    }));

    updateActiveHeadings();
}

watch(
    () => props.source,
    (source) => {
        if (source) {
            build(source);
        }
    },
    { immediate: true, flush: 'post' },
);

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
    cancelAnimationFrame(frame);
});
</script>

<template>
    <details
        v-if="variant === 'inline' && headings.length > 1"
        ref="details"
        class="toc toc--inline"
    >
        <summary>Nesta página</summary>
        <ol class="mt-3">
            <li v-for="heading in headings" :key="heading.id">
                <a
                    :href="`#${heading.id}`"
                    class="toc-link"
                    :class="{ 'toc-link--nested': heading.level === 3 }"
                    @click="scrollTo($event, heading.id)"
                >
                    {{ heading.text }}
                </a>
            </li>
        </ol>
    </details>

    <nav
        v-else-if="variant === 'aside' && headings.length > 1"
        aria-labelledby="toc-title"
        class="toc"
    >
        <p
            id="toc-title"
            class="font-[family-name:var(--font-title)] text-[0.9375rem] font-bold"
        >
            Nesta página
        </p>
        <ol class="mt-4">
            <li v-for="heading in headings" :key="heading.id">
                <a
                    :href="`#${heading.id}`"
                    class="toc-link"
                    :class="{
                        'toc-link--nested': heading.level === 3,
                        'toc-link--active': activeIds.has(heading.id),
                    }"
                    :aria-current="
                        isFirstActive(heading.id) ? 'location' : undefined
                    "
                    @click="scrollTo($event, heading.id)"
                >
                    {{ heading.text }}
                </a>
            </li>
        </ol>
    </nav>
</template>

<style scoped>
.toc--inline {
    padding: 0.875rem 1.25rem;
    border: 1px solid var(--rule);
    border-radius: 8px;
}

.toc--inline summary {
    cursor: pointer;
    font-family: var(--font-title);
    font-size: 0.9375rem;
    font-weight: 700;
}

.toc--inline summary:focus-visible {
    outline: 2px solid var(--pen);
    outline-offset: 4px;
    border-radius: 2px;
}

.toc ol {
    border-left: 1px solid var(--rule);
}

.toc-link {
    position: relative;
    display: block;
    padding: 0.3rem 0 0.3rem 1.5ch;
    font-size: 0.8125rem;
    line-height: 1.5;
    color: var(--graphite);
    transition: color 150ms;
}

.toc-link:hover {
    color: var(--ink);
}

.toc-link--nested {
    padding-left: 3.5ch;
}

.toc-link--active {
    color: var(--ink);
}

/* Same language as the timeline rail: a pen-blue stretch on the line that
   spans every section on screen. */
.toc-link--active::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: -1px;
    width: 2px;
    background: var(--pen);
}
</style>
