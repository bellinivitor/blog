<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import type { Reading } from '@/types';

defineProps<{
    readings: Reading[];
}>();

/** Same anchor the post renderer uses for a cited reading (leitura-ID). */
function anchorOf(reading: Reading): string {
    return `leitura-${reading.id}`;
}

/**
 * A reading cited in a post opens here as /leituras#leitura-ID: bring it into
 * view once the list exists (the CSS :target rule highlights it).
 */
onMounted(() => {
    if (window.location.hash) {
        document
            .getElementById(decodeURIComponent(window.location.hash.slice(1)))
            ?.scrollIntoView({ block: 'center' });
    }
});

/** Where the link leads, so a reader knows before clicking. */
function hostOf(url: string): string {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}
</script>

<template>
    <Head title="Leituras" />

    <section class="max-w-[68ch] pb-12">
        <Link
            :href="BlogPostController.index()"
            class="text-sm text-[var(--graphite)] hover:text-[var(--ink)]"
        >
            Todos os posts
        </Link>
        <h1
            class="mt-4 font-[family-name:var(--font-title)] text-[2rem] leading-tight font-bold tracking-[-0.02em]"
        >
            Leituras
        </h1>
        <p class="mt-2 text-[var(--graphite)]">
            Livros e artigos que recomendo, dos mais recentes aos mais antigos.
        </p>
    </section>

    <ol
        v-if="readings.length"
        class="max-w-[68ch] divide-y divide-[var(--rule)] border-y border-[var(--rule)]"
    >
        <li
            v-for="reading in readings"
            :id="anchorOf(reading)"
            :key="reading.id"
            class="reading-item"
        >
            <a
                :href="reading.url"
                target="_blank"
                rel="noopener"
                class="reading"
            >
                <span class="reading-title">{{ reading.title }}</span>
                <span class="reading-host">{{ hostOf(reading.url) }} ↗</span>
            </a>
        </li>
    </ol>
    <p v-else class="text-[var(--graphite)]">Ainda não há leituras aqui.</p>
</template>

<style scoped>
.reading-item {
    scroll-margin-block: 6rem;
}

/* The reading a post pointed at. */
.reading-item:target {
    margin-inline: -1.5ch;
    padding-inline: 1.5ch;
    border-radius: 8px;
    background: color-mix(in srgb, var(--pen) 9%, transparent);
    box-shadow: inset 3px 0 0 var(--pen);
}

.reading {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.25rem 2ch;
    padding-block: 1rem;
}

.reading-title {
    font-family: var(--font-title);
    font-weight: 700;
    text-decoration: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 1px;
    text-underline-offset: 0.3em;
    transition: text-decoration-color 150ms;
}

.reading:hover .reading-title,
.reading:focus-visible .reading-title {
    text-decoration-color: var(--pen);
}

.reading-host {
    font-size: 0.875rem;
    color: var(--graphite);
}

@media (prefers-reduced-motion: reduce) {
    .reading-title {
        transition: none;
    }
}
</style>
