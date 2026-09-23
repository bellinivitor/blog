<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogTagController from '@/actions/App/Http/Controllers/Blog/BlogTagController';
import BackToPosts from '@/components/blog/BackToPosts.vue';
import TableOfContents from '@/components/blog/TableOfContents.vue';
import { useArticleEnhancements } from '@/composables/useArticleEnhancements';
import { formatLongDate } from '@/lib/blogDates';
import type { PublishedPost } from '@/types';

defineProps<{
    /** In a preview, drafts have no publication date yet. */
    post: Omit<PublishedPost, 'published_at'> & { published_at: string | null };
    content: string;
    previous: PublishedPost | null;
    next: PublishedPost | null;
    related: PublishedPost[];
    /** Present when the author previews the post from the panel. */
    preview?: { editUrl: string };
}>();

const body = useTemplateRef<HTMLElement>('body');

useArticleEnhancements(body);
</script>

<template>
    <Head :title="post.title" />

    <!-- Keyed by slug so moving to another post rebuilds the TOC and enhancements. -->
    <div :key="post.slug">
        <p v-if="preview" class="preview-banner" role="status">
            Pré-visualização: é assim que o post vai ficar publicado.
            <a :href="preview.editUrl" class="blog-link"
                >Voltar para a edição</a
            >
        </p>

        <!-- On wide screens the grid grows past the column so the TOC sits in the right margin. -->
        <article
            class="xl:grid xl:w-[98ch] xl:grid-cols-[minmax(0,68ch)_24ch] xl:gap-x-[6ch]"
        >
            <header class="max-w-[68ch] pb-12 xl:col-start-1">
                <BackToPosts />
                <h1
                    class="mt-6 font-[family-name:var(--font-title)] text-[clamp(1.75rem,4.5vw,2.375rem)] leading-[1.2] font-bold tracking-[-0.02em] text-balance"
                >
                    {{ post.title }}
                </h1>
                <p
                    class="mt-5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-[var(--graphite)]"
                >
                    <time
                        v-if="post.published_at"
                        :datetime="post.published_at"
                    >
                        {{ formatLongDate(post.published_at) }}
                    </time>
                    <span v-else>Não publicado</span>
                    <span v-if="post.revised_at">
                        atualizado em
                        <time :datetime="post.revised_at">{{
                            formatLongDate(post.revised_at)
                        }}</time>
                    </span>
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

                <div class="mt-8 xl:hidden">
                    <TableOfContents :source="body" variant="inline" />
                </div>
            </header>

            <!-- Rendered server-side from the author's Markdown; raw HTML is escaped. -->
            <div
                ref="body"
                class="blog-prose xl:col-start-1 xl:row-start-2"
                v-html="content"
            />

            <aside class="hidden xl:col-start-2 xl:row-start-2 xl:block">
                <div class="sticky top-12">
                    <TableOfContents :source="body" />
                </div>
            </aside>
        </article>

        <nav
            v-if="related.length || previous || next"
            aria-label="Continue lendo"
            class="mt-20 max-w-[68ch] border-t border-[var(--rule)] pt-10"
        >
            <section v-if="related.length">
                <h2
                    class="font-[family-name:var(--font-title)] text-[1.125rem] font-bold"
                >
                    Relacionados
                </h2>
                <ul class="mt-4 space-y-5">
                    <li v-for="item in related" :key="item.slug">
                        <Link
                            :href="BlogPostController.show(item.slug)"
                            class="continue-title"
                        >
                            {{ item.title }}
                        </Link>
                        <p
                            v-if="item.excerpt"
                            class="mt-1 text-[0.9375rem] text-[var(--graphite)]"
                        >
                            {{ item.excerpt }}
                        </p>
                    </li>
                </ul>
            </section>

            <div
                v-if="previous || next"
                class="grid gap-6 sm:grid-cols-2"
                :class="{ 'mt-12': related.length }"
            >
                <Link
                    v-if="previous"
                    :href="BlogPostController.show(previous.slug)"
                    rel="prev"
                    class="continue-step"
                >
                    <span class="continue-label">Post anterior</span>
                    <span class="continue-title">{{ previous.title }}</span>
                </Link>
                <Link
                    v-if="next"
                    :href="BlogPostController.show(next.slug)"
                    rel="next"
                    class="continue-step sm:col-start-2 sm:text-right"
                >
                    <span class="continue-label">Próximo post</span>
                    <span class="continue-title">{{ next.title }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.preview-banner {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 2ch;
    margin-bottom: 2.5rem;
    padding: 0.75rem 1.25rem;
    border: 1px dashed color-mix(in srgb, var(--pen) 55%, transparent);
    border-radius: 8px;
    background: color-mix(in srgb, var(--pen) 6%, transparent);
    font-size: 0.875rem;
}

.continue-title {
    font-family: var(--font-title);
    font-weight: 700;
    text-decoration: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 1px;
    text-underline-offset: 0.3em;
    transition: text-decoration-color 150ms;
}

.continue-step {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 1rem 1.25rem;
    border: 1px solid var(--rule);
    border-radius: 8px;
    transition: border-color 150ms;
}

.continue-label {
    font-size: 0.8125rem;
    color: var(--graphite);
}

a:hover .continue-title,
.continue-title:hover,
a:focus-visible .continue-title {
    text-decoration-color: var(--pen);
}

.continue-step:hover {
    border-color: color-mix(in srgb, var(--pen) 50%, transparent);
}

@media (prefers-reduced-motion: reduce) {
    .continue-title,
    .continue-step {
        transition: none;
    }
}
</style>
