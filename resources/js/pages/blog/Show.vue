<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogTagController from '@/actions/App/Http/Controllers/Blog/BlogTagController';
import { formatLongDate } from '@/lib/blogDates';
import type { PublishedPost } from '@/types';

defineProps<{
    post: PublishedPost;
    content: string;
}>();
</script>

<template>
    <Head :title="post.title" />

    <article>
        <header class="max-w-[68ch] pb-12">
            <Link
                :href="BlogPostController.index()"
                class="text-sm text-[var(--graphite)] hover:text-[var(--ink)]"
            >
                Todos os posts
            </Link>
            <h1
                class="mt-6 font-[family-name:var(--font-title)] text-[clamp(1.75rem,4.5vw,2.375rem)] leading-[1.2] font-bold tracking-[-0.02em] text-balance"
            >
                {{ post.title }}
            </h1>
            <p
                class="mt-5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-[var(--graphite)]"
            >
                <time :datetime="post.published_at">
                    {{ formatLongDate(post.published_at) }}
                </time>
                <span>{{ post.reading_minutes }} min de leitura</span>
                <Link
                    v-for="tag in post.tags"
                    :key="tag.id"
                    :href="BlogTagController.show(tag.slug)"
                    class="hover:text-[var(--pen)]"
                >
                    #{{ tag.slug }}
                </Link>
            </p>
        </header>

        <!-- Rendered server-side from the author's Markdown; raw HTML is escaped. -->
        <div class="blog-prose" v-html="content" />
    </article>
</template>
