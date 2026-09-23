<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

const { appearance, updateAppearance } = useAppearance();

const ORDER: Appearance[] = ['system', 'light', 'dark'];

const LABELS: Record<Appearance, string> = {
    system: 'tema do sistema',
    light: 'tema claro',
    dark: 'tema escuro',
};

const nextAppearance = computed<Appearance>(
    () => ORDER[(ORDER.indexOf(appearance.value) + 1) % ORDER.length],
);

const label = computed(
    () =>
        `Usando ${LABELS[appearance.value]}. Mudar para ${LABELS[nextAppearance.value]}.`,
);

const icon = computed(
    () => ({ system: Monitor, light: Sun, dark: Moon })[appearance.value],
);
</script>

<template>
    <button
        type="button"
        class="theme-toggle"
        :aria-label="label"
        :title="label"
        @click="updateAppearance(nextAppearance)"
    >
        <component :is="icon" class="size-4" aria-hidden="true" />
    </button>
</template>

<style scoped>
.theme-toggle {
    display: inline-flex;
    align-self: center;
    padding: 0.25rem;
    margin: -0.25rem;
    border-radius: 4px;
    color: inherit;
    cursor: pointer;
}

.theme-toggle:hover {
    color: var(--ink);
}

.theme-toggle:focus-visible {
    outline: 2px solid var(--pen);
    outline-offset: 2px;
}
</style>
