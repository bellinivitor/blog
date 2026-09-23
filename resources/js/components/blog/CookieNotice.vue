<script lang="ts">
export const COOKIE_NOTICE_STORAGE_KEY = 'cookie-notice-dismissed';
</script>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import BlogPrivacyController from '@/actions/App/Http/Controllers/Blog/BlogPrivacyController';

/**
 * Informational notice, not a consent prompt: the blog only sets essential
 * cookies, so there is nothing to accept or refuse. Hidden until mounted so
 * the server render and returning readers never flash it.
 */
const isVisible = ref(false);

onMounted(() => {
    try {
        isVisible.value =
            localStorage.getItem(COOKIE_NOTICE_STORAGE_KEY) === null;
    } catch {
        isVisible.value = true;
    }
});

function dismiss(): void {
    isVisible.value = false;

    try {
        localStorage.setItem(COOKIE_NOTICE_STORAGE_KEY, '1');
    } catch {
        // Storage blocked: the notice simply shows again next visit.
    }
}
</script>

<template>
    <aside
        v-if="isVisible"
        aria-label="Aviso sobre cookies"
        class="cookie-notice"
    >
        <p>
            Este blog usa apenas cookies essenciais, sem rastreamento.
            <Link :href="BlogPrivacyController.show()" class="blog-link">
                Saiba mais
            </Link>
        </p>
        <button type="button" class="cookie-notice-button" @click="dismiss">
            Entendi
        </button>
    </aside>
</template>

<style scoped>
.cookie-notice {
    position: fixed;
    inset-inline: 1rem;
    bottom: 1rem;
    z-index: 40;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem 2ch;
    max-width: 60ch;
    margin-inline: auto;
    padding: 0.875rem 1.25rem;
    border: 1px solid var(--rule);
    border-radius: 8px;
    background: var(--panel);
    font-size: 0.875rem;
    line-height: 1.6;
    box-shadow: 0 8px 24px -12px rgb(0 0 0 / 0.25);
}

.cookie-notice-button {
    padding: 0.25rem 1.25ch;
    border: 1px solid var(--pen);
    border-radius: 4px;
    color: var(--pen);
    cursor: pointer;
}

.cookie-notice-button:hover {
    background: color-mix(in srgb, var(--pen) 10%, transparent);
}

.cookie-notice-button:focus-visible {
    outline: 2px solid var(--pen);
    outline-offset: 2px;
}
</style>
