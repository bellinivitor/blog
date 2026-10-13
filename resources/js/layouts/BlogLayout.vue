<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import BlogFeedController from "@/actions/App/Http/Controllers/Blog/BlogFeedController";
import BlogPostController from "@/actions/App/Http/Controllers/Blog/BlogPostController";
import BlogPrivacyController from "@/actions/App/Http/Controllers/Blog/BlogPrivacyController";
import BlogReadingController from "@/actions/App/Http/Controllers/Blog/BlogReadingController";
import CookieNotice from "@/components/blog/CookieNotice.vue";
import SearchDialog from "@/components/blog/SearchDialog.vue";
import ThemeToggle from "@/components/blog/ThemeToggle.vue";
import { dashboard } from "@/routes";
import "../../css/blog.css";

const blog = computed(() => usePage().props.blog);
const year = new Date().getFullYear();
const feedUrl = BlogFeedController.index().url;
const externalLinks = computed(() => [
    { label: "Apps", url: "https://apps.vitorbellini.com.br/" },
    ...blog.value.links,
]);
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
                class="mt-24 border-t border-[var(--rule)] pt-8 pb-10 text-sm text-[var(--graphite)]"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between sm:gap-8"
                >
                    <nav
                        aria-label="Rodapé"
                        class="flex flex-wrap gap-x-5 gap-y-2"
                    >
                        <Link
                            :href="BlogReadingController.index()"
                            class="hover:text-[var(--ink)]"
                        >
                            Leituras
                        </Link>
                        <a :href="feedUrl" class="hover:text-[var(--ink)]">
                            RSS
                        </a>
                        <a
                            v-for="link in externalLinks"
                            :key="link.url"
                            :href="link.url"
                            target="_blank"
                            rel="noopener"
                            class="group hover:text-[var(--ink)]"
                        >
                            {{ link.label
                            }}<span
                                aria-hidden="true"
                                class="ml-0.5 opacity-60 group-hover:text-[var(--pen)] group-hover:opacity-100"
                                >↗</span
                            >
                        </a>
                    </nav>
                    <a
                        href="https://buymeacoffee.com/vitorbellini"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-full bg-[#FFDD00] px-3 py-1 font-medium whitespace-nowrap text-black transition-[filter] hover:brightness-95 sm:self-auto"
                    >
                        <span aria-hidden="true">☕</span>
                        Buy me a coffee
                    </a>
                </div>

                <div
                    class="mt-8 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2 text-xs"
                >
                    <span>© {{ year }} {{ blog.author }}</span>
                    <span class="flex gap-x-5">
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
                </div>
            </footer>
        </div>

        <CookieNotice />
    </div>
</template>
