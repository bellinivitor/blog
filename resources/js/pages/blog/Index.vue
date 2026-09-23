<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PostTimeline from '@/components/blog/PostTimeline.vue';
import type { PublishedPost } from '@/types';

defineProps<{
    posts: PublishedPost[];
}>();

const blog = computed(() => usePage().props.blog);
</script>

<template>
    <Head :title="blog.author" />

    <section class="max-w-[60ch] pb-20">
        <h1
            class="font-[family-name:var(--font-title)] text-[clamp(1.875rem,5vw,2.625rem)] leading-[1.15] font-bold tracking-[-0.02em] text-balance"
        >
            {{ blog.headline }}
        </h1>
        <p class="mt-6 text-[var(--graphite)]">{{ blog.bio }}</p>
    </section>

    <PostTimeline v-if="posts.length" :posts="posts" />
    <p v-else class="text-[var(--graphite)]">Ainda não há posts publicados.</p>
</template>
