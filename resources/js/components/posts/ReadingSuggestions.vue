<script setup lang="ts">
import type { Reading } from '@/types';

defineProps<{
    readings: Reading[];
    highlighted: number;
    loading: boolean;
    /** Viewport position of the "@" being completed. */
    position: { left: number; top: number };
}>();

const emit = defineEmits<{
    select: [reading: Reading];
    highlight: [index: number];
}>();
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed z-50 w-80 rounded-md border bg-popover p-1 text-sm text-popover-foreground shadow-md"
            :style="{
                left: `${position.left}px`,
                top: `${position.top + 4}px`,
            }"
        >
            <ul
                v-if="readings.length"
                id="reading-suggestions"
                role="listbox"
                aria-label="Readings"
            >
                <li
                    v-for="(reading, index) in readings"
                    :id="`reading-suggestion-${reading.id}`"
                    :key="reading.id"
                    role="option"
                    :aria-selected="index === highlighted"
                    class="cursor-pointer rounded-sm px-2 py-1.5"
                    :class="index === highlighted && 'bg-accent'"
                    @mouseenter="emit('highlight', index)"
                    @mousedown.prevent="emit('select', reading)"
                >
                    <span class="block truncate font-medium">
                        {{ reading.title }}
                    </span>
                    <span class="block truncate text-xs text-muted-foreground">
                        {{ reading.url }}
                    </span>
                </li>
            </ul>
            <p v-else class="px-2 py-1.5 text-muted-foreground">
                {{ loading ? 'Searching…' : 'No readings match.' }}
            </p>
            <p
                class="mt-1 border-t px-2 pt-1.5 pb-0.5 text-xs text-muted-foreground"
            >
                ↑↓ to choose · Enter to insert · Esc to close
            </p>
        </div>
    </Teleport>
</template>
