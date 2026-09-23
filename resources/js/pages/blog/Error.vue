<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import type { PublishedPost } from '@/types';

type ErrorStatus = 403 | 404 | 500 | 503;

const props = defineProps<{
    status: ErrorStatus;
    /** Only filled on a 404: somewhere to go instead. */
    suggestions: PublishedPost[];
}>();

const MESSAGES: Record<ErrorStatus, { title: string; description: string }> = {
    403: {
        title: 'Acesso restrito',
        description: 'Esta página existe, mas não está aberta para você.',
    },
    404: {
        title: 'Página não encontrada',
        description:
            'O endereço pode ter mudado, ou o post saiu do ar. Talvez um destes sirva.',
    },
    500: {
        title: 'Algo quebrou por aqui',
        description:
            'O erro foi do servidor, não seu. Tente de novo em alguns instantes.',
    },
    503: {
        title: 'Em manutenção',
        description: 'O blog volta em alguns minutos.',
    },
};

const message = computed(() => MESSAGES[props.status] ?? MESSAGES[500]);
</script>

<template>
    <Head :title="message.title" />

    <section class="max-w-[60ch] pb-16">
        <p
            class="font-[family-name:var(--font-title)] text-[clamp(4rem,14vw,6.5rem)] leading-none font-bold tracking-[-0.04em] text-[var(--pen)]"
            aria-hidden="true"
        >
            {{ status }}
        </p>
        <h1
            class="mt-6 font-[family-name:var(--font-title)] text-[clamp(1.5rem,4vw,2rem)] leading-tight font-bold tracking-[-0.02em]"
        >
            {{ message.title }}
        </h1>
        <p class="mt-4 text-[var(--graphite)]">{{ message.description }}</p>
    </section>

    <section
        v-if="suggestions.length"
        aria-label="Sugestões de leitura"
        class="max-w-[68ch] border-t border-[var(--rule)] pt-10"
    >
        <ul class="space-y-6">
            <li v-for="post in suggestions" :key="post.slug">
                <Link
                    :href="BlogPostController.show(post.slug)"
                    class="suggestion-title"
                >
                    {{ post.title }}
                </Link>
                <p
                    v-if="post.excerpt"
                    class="mt-1 text-[0.9375rem] text-[var(--graphite)]"
                >
                    {{ post.excerpt }}
                </p>
                <p class="mt-1 text-sm text-[var(--graphite)]">
                    {{ post.reading_minutes }} min
                </p>
            </li>
        </ul>
    </section>

    <p class="mt-12 text-sm">
        <Link :href="BlogPostController.index()" class="blog-link">
            Ver todos os posts
        </Link>
    </p>
</template>

<style scoped>
.suggestion-title {
    font-family: var(--font-title);
    font-weight: 700;
    text-decoration: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 1px;
    text-underline-offset: 0.3em;
    transition: text-decoration-color 150ms;
}

.suggestion-title:hover,
.suggestion-title:focus-visible {
    text-decoration-color: var(--pen);
}

@media (prefers-reduced-motion: reduce) {
    .suggestion-title {
        transition: none;
    }
}
</style>
