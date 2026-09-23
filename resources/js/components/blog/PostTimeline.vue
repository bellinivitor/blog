<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogTagController from '@/actions/App/Http/Controllers/Blog/BlogTagController';
import { formatShortDate, yearOf } from '@/lib/blogDates';
import type { PublishedPost } from '@/types';

const props = defineProps<{
    posts: PublishedPost[];
}>();

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
            <h2 class="timeline-row timeline-year">
                <span class="timeline-cell" />
                <span class="timeline-cell timeline-rail" aria-hidden="true">
                    <span class="timeline-branch" />
                </span>
                <span class="timeline-cell timeline-year-label">
                    <span class="timeline-year-number">{{ group.year }}</span>
                    <span class="timeline-year-count">
                        {{ group.posts.length }}
                        {{ group.posts.length === 1 ? 'post' : 'posts' }}
                    </span>
                </span>
            </h2>

            <ol>
                <li
                    v-for="post in group.posts"
                    :key="post.slug"
                    class="timeline-row timeline-post"
                >
                    <time class="timeline-date" :datetime="post.published_at">
                        {{ formatShortDate(post.published_at) }}
                    </time>
                    <span class="timeline-rail" aria-hidden="true">
                        <span class="timeline-node" />
                    </span>
                    <div class="pb-9">
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
                            v-if="post.tags.length"
                            class="mt-2 flex flex-wrap gap-x-3 text-sm"
                        >
                            <Link
                                v-for="tag in post.tags"
                                :key="tag.id"
                                :href="BlogTagController.show(tag.slug)"
                                class="text-[var(--graphite)] hover:text-[var(--pen)]"
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
    text-align: right;
    font-size: 0.875rem;
    line-height: 1.75rem;
    color: var(--graphite);
    font-variant-numeric: tabular-nums;
}

/* A new year opens a section: extra room above, the year set in the reading
   column, and a hairline branching off the rail to the end of the column. */
.timeline-year {
    --year-gap: 2.25rem;
}

.timeline > li:first-child .timeline-year {
    --year-gap: 0rem;
}

.timeline-year > .timeline-cell {
    padding-top: var(--year-gap);
    padding-bottom: 1.75rem;
}

.timeline-year-label {
    display: flex;
    align-items: baseline;
    gap: 2ch;
    line-height: 1.75rem;
}

.timeline-year-label::after {
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

.timeline > li:first-child > .timeline-year .timeline-rail::before {
    top: 0.875rem;
}

.timeline > li:last-child li:last-child .timeline-rail::before {
    bottom: calc(100% - 0.875rem);
}

.timeline-node,
.timeline-branch {
    position: absolute;
    left: 50%;
    top: 0.875rem;
    translate: -50% -50%;
}

.timeline-year .timeline-branch {
    top: calc(var(--year-gap) + 0.875rem);
}

/* Short connector from the year marker into the reading column. */
.timeline-year .timeline-rail::after {
    content: '';
    position: absolute;
    top: calc(var(--year-gap) + 0.875rem);
    left: 50%;
    right: 0;
    height: 1px;
    background: color-mix(in srgb, var(--pen) 45%, transparent);
}

.timeline-node {
    width: 0.625rem;
    height: 0.625rem;
    border: 1.5px solid var(--pen);
    border-radius: 999px;
    background: var(--paper);
}

.timeline-branch {
    z-index: 1;
    width: 0.625rem;
    height: 0.625rem;
    background: var(--pen);
    rotate: 45deg;
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

.timeline-post:hover .timeline-node {
    background: var(--pen);
}

.timeline-title:hover,
.timeline-title:focus-visible {
    text-decoration-color: var(--pen);
}

@media (max-width: 640px) {
    .timeline-row {
        grid-template-columns: 6ch 3.5ch minmax(0, 1fr);
    }
}
</style>
