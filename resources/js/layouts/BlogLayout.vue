<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import '../../css/blog.css';

const blog = computed(() => usePage().props.blog);
const year = new Date().getFullYear();
</script>

<template>
    <div class="blog">
        <div class="mx-auto flex min-h-svh max-w-[76ch] flex-col px-6">
            <header
                class="flex items-baseline justify-between gap-6 pt-10 pb-16 sm:pt-14"
            >
                <Link
                    :href="BlogPostController.index()"
                    class="font-[family-name:var(--font-title)] text-[1.0625rem] font-bold tracking-tight"
                >
                    {{ blog.author }}
                </Link>
                <nav class="flex gap-5 text-sm text-[var(--graphite)]">
                    <a
                        v-for="link in blog.links"
                        :key="link.url"
                        :href="link.url"
                        class="hover:text-[var(--ink)]"
                        rel="me noopener"
                        target="_blank"
                    >
                        {{ link.label }}
                    </a>
                </nav>
            </header>

            <main class="flex-1">
                <slot />
            </main>

            <footer
                class="mt-24 border-t border-[var(--rule)] py-8 text-sm text-[var(--graphite)]"
            >
                {{ blog.author }}, {{ year }}.
            </footer>
        </div>
    </div>
</template>
