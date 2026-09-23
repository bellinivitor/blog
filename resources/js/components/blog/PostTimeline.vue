<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogTagController from '@/actions/App/Http/Controllers/Blog/BlogTagController';
import { formatShortDate, yearOf } from '@/lib/blogDates';
import type { PublishedPost } from '@/types';

const props = defineProps<{
    posts: PublishedPost[];
    /** Mark the newest post as HEAD, like `git log` does. */
    showHead?: boolean;
}>();

const headSlug = computed(() =>
    props.showHead ? (props.posts[0]?.slug ?? null) : null,
);

const years = computed(() => {
    const groups = new Map<number, PublishedPost[]>();

    for (const post of props.posts) {
        const year = yearOf(post.published_at);
        groups.set(year, [...(groups.get(year) ?? []), post]);
    }

    return [...groups.entries()].map(([year, posts]) => ({ year, posts }));
});
</script>

<template>
    <ol class="timeline">
        <li v-for="group in years" :key="group.year">
            <h2 class="timeline-year">
                <span class="timeline-year-number">{{ group.year }}</span>
                <span class="timeline-year-count">
                    {{ group.posts.length }}
                    {{ group.posts.length === 1 ? 'post' : 'posts' }}
                </span>
            </h2>

            <ol>
                <li
                    v-for="post in group.posts"
                    :key="post.slug"
                    class="timeline-row timeline-post"
                    :class="{ 'timeline-post--head': post.slug === headSlug }"
                >
                    <span class="timeline-cell timeline-date">
                        <time :datetime="post.published_at">
                            {{ formatShortDate(post.published_at) }}
                        </time>
                        <span
                            v-if="post.slug === headSlug"
                            class="timeline-head-label"
                            title="Post mais recente"
                        >
                            HEAD
                        </span>
                    </span>
                    <span
                        class="timeline-cell timeline-rail"
                        aria-hidden="true"
                    >
                        <span class="timeline-node" />
                    </span>
                    <div class="timeline-cell timeline-body">
                        <Link
                            :href="BlogPostController.show(post.slug)"
                            class="timeline-title"
                        >
                            {{ post.title }}
                        </Link>
                        <p
                            v-if="post.excerpt"
                            class="mt-1 text-[0.9375rem] text-[var(--graphite)]"
                        >
                            {{ post.excerpt }}
                        </p>
                        <p
                            class="timeline-meta mt-2 flex flex-wrap gap-x-3 text-sm text-[var(--graphite)]"
                        >
                            <span>{{ post.reading_minutes }} min</span>
                            <Link
                                v-for="tag in post.tags"
                                :key="tag.id"
                                :href="BlogTagController.show(tag.slug)"
                                class="timeline-tag hover:text-[var(--pen)]"
                            >
                                #{{ tag.slug }}
                            </Link>
                        </p>
                    </div>
                </li>
            </ol>
        </li>
    </ol>
</template>

<style scoped>
.timeline-row {
    display: grid;
    grid-template-columns: 7ch 5ch minmax(0, 1fr);
}

.timeline-date {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    font-size: 0.875rem;
    line-height: 1.75rem;
    color: var(--graphite);
    font-variant-numeric: tabular-nums;
}

/* Each post row: padding lives in the cells so the rail stays continuous. */
.timeline-post {
    --row-pad: 1.125rem;

    position: relative;
    isolation: isolate;
}

.timeline-post > .timeline-cell {
    padding-block: var(--row-pad);
}

/* The whole row is the link target; tags stay independently clickable. */
.timeline-title::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 1;
}

.timeline-tag {
    position: relative;
    z-index: 2;
}

/* A faint pen wash answers hover and keyboard focus. */
.timeline-post::before {
    content: '';
    position: absolute;
    inset: 0 -1.5ch 0 -1.5ch;
    z-index: -1;
    border-radius: 8px;
    background: color-mix(in srgb, var(--pen) 7%, transparent);
    opacity: 0;
    transition: opacity 150ms;
}

.timeline-post:hover::before,
.timeline-post:has(.timeline-title:focus-visible)::before {
    opacity: 1;
}

.timeline-post:has(.timeline-title:focus-visible)::before {
    outline: 2px solid var(--pen);
    outline-offset: -2px;
}

.timeline-title:focus-visible {
    outline: none;
}

.timeline-head-label {
    font-size: 0.6875rem;
    line-height: 1.25rem;
    letter-spacing: 0.02em;
    color: var(--pen);
}

/* A new year is a centred divider, apart from the rail: each year gets its
   own rail segment, from its first post to its last. */
.timeline-year {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 1.5ch;
    padding: 2.25rem 0 1rem;
    line-height: 1.75rem;
}

.timeline > li:first-child .timeline-year {
    padding-top: 0;
}

.timeline-year::before,
.timeline-year::after {
    content: '';
    flex: 1;
    align-self: center;
    height: 1px;
    background: var(--rule);
}

.timeline-year-number {
    font-family: var(--font-title);
    font-size: 1.375rem;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.timeline-year-count {
    font-size: 0.8125rem;
    color: var(--graphite);
}

/* The rail: one continuous line, like `git log --graph`. */
.timeline-rail {
    position: relative;
}

.timeline-rail::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 1px;
    background: color-mix(in srgb, var(--pen) 45%, transparent);
}

.timeline-post:first-child .timeline-rail::before {
    top: calc(var(--row-pad) + 0.875rem);
}

.timeline-post:last-child .timeline-rail::before {
    bottom: calc(100% - var(--row-pad) - 0.875rem);
}

.timeline-node {
    position: absolute;
    left: 50%;
    top: 0.875rem;
    translate: -50% -50%;
}

.timeline-post .timeline-node {
    top: calc(var(--row-pad) + 0.875rem);
}

.timeline-node {
    width: 0.625rem;
    height: 0.625rem;
    border: 1.5px solid var(--pen);
    border-radius: 999px;
    background: var(--paper);
    transition: background-color 150ms;
}

.timeline-post--head .timeline-node {
    width: 0.75rem;
    height: 0.75rem;
    background: var(--pen);
    box-shadow:
        0 0 0 3px var(--paper),
        0 0 0 4px color-mix(in srgb, var(--pen) 45%, transparent);
}

.timeline-title {
    font-family: var(--font-title);
    font-size: 1.0625rem;
    font-weight: 700;
    line-height: 1.75rem;
    text-decoration: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 1px;
    text-underline-offset: 0.3em;
    transition: text-decoration-color 150ms;
}

.timeline-post:hover .timeline-node,
.timeline-post:has(.timeline-title:focus-visible) .timeline-node {
    background: var(--pen);
}

.timeline-post:hover .timeline-title,
.timeline-title:focus-visible {
    text-decoration-color: var(--pen);
}

@media (prefers-reduced-motion: reduce) {
    .timeline-post::before,
    .timeline-node,
    .timeline-title {
        transition: none;
    }
}

@media (max-width: 640px) {
    .timeline-row {
        grid-template-columns: 6ch 3.5ch minmax(0, 1fr);
    }

    .timeline-post::before {
        inset: 0 -0.75rem 0 -0.75rem;
    }
}
</style>
