<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BackToPosts from '@/components/blog/BackToPosts.vue';
import PostTimeline from '@/components/blog/PostTimeline.vue';
import type { PublishedPost, Tag } from '@/types';

defineProps<{
    tag: Tag;
    posts: PublishedPost[];
}>();
</script>

<template>
    <Head :title="tag.name" />

    <section class="pb-16">
        <BackToPosts />
        <h1
            class="mt-4 font-[family-name:var(--font-title)] text-[2rem] leading-tight font-bold tracking-[-0.02em]"
        >
            #{{ tag.slug }}
        </h1>
        <p class="mt-2 text-[var(--graphite)]">
            {{ posts.length }}
            {{ posts.length === 1 ? 'post' : 'posts' }} sobre {{ tag.name }}.
        </p>
    </section>

    <PostTimeline v-if="posts.length" :posts="posts" />
</template>
