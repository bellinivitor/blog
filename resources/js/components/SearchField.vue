<script setup lang="ts">
import { Search } from '@lucide/vue';

defineProps<{
    placeholder: string;
}>();

const model = defineModel<string>({ required: true });
</script>

<template>
    <label class="relative block min-w-0">
        <span class="sr-only">{{ placeholder }}</span>
        <Search
            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
            aria-hidden="true"
        />
        <input
            v-model="model"
            type="search"
            :placeholder="placeholder"
            class="h-9 w-full rounded-md border border-input bg-transparent pr-3 pl-8 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
        />
    </label>
</template>
