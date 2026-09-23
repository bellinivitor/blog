<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

type Heading = {
    id: string;
    text: string;
    level: 2 | 3;
};

const props = defineProps<{
    /** Element holding the rendered article, whose h2/h3 carry ids. */
    source: HTMLElement | null;
}>();

const headings = ref<Heading[]>([]);
const activeId = ref<string | null>(null);

/** Share of the viewport, from the top, where a heading counts as "being read". */
const READING_LINE = 0.3;

let elements: HTMLElement[] = [];
let lockedUntil = 0;
let frame = 0;

function collectHeadings(root: HTMLElement): HTMLElement[] {
    return [...root.querySelectorAll<HTMLElement>('h2[id], h3[id]')];
}

function updateActiveHeading(): void {
    frame = 0;

    if (Date.now() < lockedUntil || elements.length === 0) {
        return;
    }

    const reachedBottom =
        window.innerHeight + window.scrollY >=
        document.documentElement.scrollHeight - 2;

    if (reachedBottom) {
        activeId.value = elements[elements.length - 1].id;

        return;
    }

    const line = window.innerHeight * READING_LINE;
    const passed = elements.filter(
        (el) => el.getBoundingClientRect().top <= line,
    );

    activeId.value = (passed.at(-1) ?? elements[0]).id;
}

function onScroll(): void {
    if (!frame) {
        frame = requestAnimationFrame(updateActiveHeading);
    }
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

    // Keep the clicked item highlighted while the smooth scroll runs, even
    // when the heading cannot reach the top (end of the page).
    lockedUntil = Date.now() + (reduceMotion ? 0 : 900);
    activeId.value = id;

    target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
    history.replaceState(null, '', `#${encodeURIComponent(id)}`);
}

function build(source: HTMLElement): void {
    elements = collectHeadings(source);

    headings.value = elements.map((el) => ({
        id: el.id,
        text: el.textContent?.trim() ?? '',
        level: el.tagName === 'H3' ? 3 : 2,
    }));

    updateActiveHeading();
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

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    cancelAnimationFrame(frame);
});
</script>

<template>
    <nav v-if="headings.length > 1" aria-labelledby="toc-title" class="toc">
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
                        'toc-link--active': heading.id === activeId,
                    }"
                    :aria-current="
                        heading.id === activeId ? 'location' : undefined
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

/* Same language as the timeline rail: a pen-blue mark on the line. */
.toc-link--active::before {
    content: '';
    position: absolute;
    top: 0.3rem;
    bottom: 0.3rem;
    left: -1px;
    width: 2px;
    background: var(--pen);
}
</style>
