<script lang="ts">
export const LIKED_POSTS_STORAGE_KEY = 'liked-posts';
</script>

<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import BlogPostLikeController from '@/actions/App/Http/Controllers/Blog/BlogPostLikeController';

const props = defineProps<{
    slug: string;
    likes: number;
}>();

/**
 * Readers are anonymous, so the browser remembers which posts it liked:
 * the heart stays filled and the button cannot like the same post twice.
 * Read on mount so the server render never guesses.
 */
const count = ref(props.likes);
const isLiked = ref(false);
const http = useHttp<Record<string, never>, { likes: number }>({});

function likedSlugs(): string[] {
    try {
        const stored = JSON.parse(
            localStorage.getItem(LIKED_POSTS_STORAGE_KEY) ?? '[]',
        );

        return Array.isArray(stored) ? stored : [];
    } catch {
        return [];
    }
}

function rememberLike(): void {
    try {
        localStorage.setItem(
            LIKED_POSTS_STORAGE_KEY,
            JSON.stringify([...new Set([...likedSlugs(), props.slug])]),
        );
    } catch {
        // Storage blocked: the heart just shows empty again next visit.
    }
}

onMounted(() => {
    isLiked.value = likedSlugs().includes(props.slug);
});

async function like(): Promise<void> {
    if (isLiked.value || http.processing) {
        return;
    }

    isLiked.value = true;
    count.value++;

    try {
        const response = await http.post(
            BlogPostLikeController.store(props.slug).url,
        );

        count.value = response.likes;
        rememberLike();
    } catch (error) {
        count.value--;

        // 429: this visitor already liked the post today, so keep the heart.
        if (
            (error as { response?: { status?: number } }).response?.status ===
            429
        ) {
            rememberLike();

            return;
        }

        isLiked.value = false;
    }
}
</script>

<template>
    <div class="post-like">
        <button
            type="button"
            class="post-like-button"
            :class="{ 'is-liked': isLiked }"
            :aria-pressed="isLiked"
            :aria-label="isLiked ? 'Você curtiu este post' : 'Curtir este post'"
            :disabled="isLiked"
            @click="like"
        >
            <Heart
                :size="20"
                :stroke-width="1.75"
                :fill="isLiked ? 'currentColor' : 'none'"
                aria-hidden="true"
            />
        </button>
        <span class="post-like-count" aria-live="polite">
            {{ count }} {{ count === 1 ? 'curtida' : 'curtidas' }}
        </span>
    </div>
</template>

<style scoped>
.post-like {
    display: flex;
    align-items: center;
    gap: 1ch;
    font-size: 0.875rem;
    color: var(--graphite);
}

.post-like-button {
    display: inline-grid;
    place-items: center;
    width: 2.5rem;
    height: 2.5rem;
    border: 1px solid var(--rule);
    border-radius: 999px;
    color: var(--graphite);
    cursor: pointer;
    transition:
        color 150ms,
        border-color 150ms,
        transform 150ms;
}

.post-like-button:hover:not(:disabled) {
    color: var(--pen);
    border-color: color-mix(in srgb, var(--pen) 50%, transparent);
}

.post-like-button:active:not(:disabled) {
    transform: scale(0.92);
}

.post-like-button:focus-visible {
    outline: 2px solid var(--pen);
    outline-offset: 2px;
}

.post-like-button.is-liked {
    color: var(--pen);
    border-color: color-mix(in srgb, var(--pen) 50%, transparent);
    cursor: default;
}

@media (prefers-reduced-motion: reduce) {
    .post-like-button {
        transition: none;
    }
}
</style>
