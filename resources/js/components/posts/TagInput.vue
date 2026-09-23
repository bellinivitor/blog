<script setup lang="ts">
import { X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { Tag } from '@/types';

const props = defineProps<{
    tags: Tag[];
    defaultSelected?: Tag[];
    name: string;
    id?: string;
}>();

const selected = ref<Tag[]>([...(props.defaultSelected ?? [])]);
const query = ref('');
const isOpen = ref(false);
const highlightedIndex = ref(0);

const suggestions = computed(() => {
    const term = query.value.trim().toLowerCase();

    return props.tags.filter(
        (tag) =>
            !selected.value.some((item) => item.id === tag.id) &&
            (term === '' || tag.name.toLowerCase().includes(term)),
    );
});

function select(tag: Tag): void {
    selected.value.push(tag);
    query.value = '';
    highlightedIndex.value = 0;
}

function remove(tag: Tag): void {
    selected.value = selected.value.filter((item) => item.id !== tag.id);
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        isOpen.value = true;
        highlightedIndex.value = Math.min(
            highlightedIndex.value + 1,
            suggestions.value.length - 1,
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const tag = suggestions.value[highlightedIndex.value];

        if (isOpen.value && tag) {
            select(tag);
        }
    } else if (event.key === 'Escape') {
        isOpen.value = false;
    } else if (event.key === 'Backspace' && query.value === '') {
        selected.value.pop();
    }
}

function onInput(): void {
    isOpen.value = true;
    highlightedIndex.value = 0;
}
</script>

<template>
    <div class="relative">
        <div
            class="flex min-h-9 w-full flex-wrap items-center gap-1.5 rounded-md border border-input bg-transparent px-2 py-1.5 shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 dark:bg-input/30"
        >
            <Badge
                v-for="tag in selected"
                :key="tag.id"
                variant="secondary"
                class="gap-1 pr-1"
            >
                {{ tag.name }}
                <button
                    type="button"
                    class="rounded-full p-0.5 hover:bg-foreground/10"
                    :aria-label="`Remove ${tag.name}`"
                    @click="remove(tag)"
                >
                    <X />
                </button>
                <input type="hidden" :name="`${name}[]`" :value="tag.id" />
            </Badge>

            <input
                :id="id"
                v-model="query"
                type="text"
                role="combobox"
                autocomplete="off"
                :aria-expanded="isOpen && suggestions.length > 0"
                class="min-w-32 flex-1 bg-transparent px-1 text-base outline-none placeholder:text-muted-foreground md:text-sm"
                :placeholder="selected.length ? '' : 'Type to add tags'"
                @input="onInput"
                @focus="isOpen = true"
                @blur="isOpen = false"
                @keydown="onKeydown"
            />
        </div>

        <ul
            v-if="isOpen"
            role="listbox"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover p-1 text-sm shadow-md"
        >
            <li
                v-for="(tag, index) in suggestions"
                :key="tag.id"
                role="option"
                :aria-selected="index === highlightedIndex"
                class="cursor-pointer rounded-sm px-2 py-1.5"
                :class="index === highlightedIndex && 'bg-accent'"
                @mousedown.prevent="select(tag)"
                @mouseenter="highlightedIndex = index"
            >
                {{ tag.name }}
            </li>
            <li
                v-if="suggestions.length === 0"
                class="px-2 py-1.5 text-muted-foreground"
            >
                {{ tags.length ? 'No matching tags.' : 'No tags yet.' }}
            </li>
        </ul>
    </div>
</template>
