<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
</script>

<template>
    <Link :href="BlogPostController.index()" class="back-to-posts">
        <ArrowLeft class="back-to-posts-arrow" aria-hidden="true" />
        Todos os posts
    </Link>
</template>

<style scoped>
.back-to-posts {
    display: inline-flex;
    align-items: center;
    gap: 0.5ch;
    font-size: 0.875rem;
    color: var(--graphite);
}

.back-to-posts:hover {
    color: var(--ink);
}

.back-to-posts-arrow {
    width: 1em;
    height: 1em;
    transition: translate 150ms;
}

/* The arrow leans the way the link goes. */
.back-to-posts:hover .back-to-posts-arrow,
.back-to-posts:focus-visible .back-to-posts-arrow {
    translate: -3px 0;
}

@media (prefers-reduced-motion: reduce) {
    .back-to-posts-arrow {
        transition: none;
    }
}
</style>
