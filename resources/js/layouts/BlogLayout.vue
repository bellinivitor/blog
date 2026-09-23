<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BlogFeedController from '@/actions/App/Http/Controllers/Blog/BlogFeedController';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogPrivacyController from '@/actions/App/Http/Controllers/Blog/BlogPrivacyController';
import BlogReadingController from '@/actions/App/Http/Controllers/Blog/BlogReadingController';
import CookieNotice from '@/components/blog/CookieNotice.vue';
import SearchDialog from '@/components/blog/SearchDialog.vue';
import ThemeToggle from '@/components/blog/ThemeToggle.vue';
import { dashboard } from '@/routes';
import '../../css/blog.css';

const blog = computed(() => usePage().props.blog);
const year = new Date().getFullYear();
const feedUrl = BlogFeedController.index().url;
</script>

<template>
    <div class="blog">
        <div class="mx-auto flex min-h-svh max-w-[76ch] flex-col px-6">
            <header
                class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-3 pt-10 pb-16 sm:pt-14"
            >
                <Link
                    :href="BlogPostController.index()"
                    class="font-[family-name:var(--font-title)] text-[1.0625rem] font-bold tracking-tight whitespace-nowrap"
                >
                    {{ blog.author }}
                </Link>
                <nav
                    class="flex items-baseline gap-5 text-sm text-[var(--graphite)]"
                >
                    <SearchDialog />
                    <ThemeToggle />
                </nav>
            </header>

            <main class="flex-1">
                <slot />
            </main>

            <footer
                class="mt-24 flex flex-col gap-3 border-t border-[var(--rule)] py-8 text-sm text-[var(--graphite)] sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"
            >
                <span>{{ blog.author }}, {{ year }}.</span>
                <span class="flex flex-wrap gap-x-5 gap-y-2">
                    <Link
                        :href="BlogReadingController.index()"
                        class="hover:text-[var(--ink)]"
                    >
                        Leituras
                    </Link>
                    <a :href="feedUrl" class="hover:text-[var(--ink)]">RSS</a>
                    <Link
                        :href="BlogPrivacyController.show()"
                        class="hover:text-[var(--ink)]"
                    >
                        Privacidade
                    </Link>
                    <Link
                        :href="dashboard()"
                        rel="nofollow"
                        class="hover:text-[var(--ink)]"
                    >
                        Painel
                    </Link>
                </span>
            </footer>
        </div>

        <CookieNotice />
    </div>
</template>
