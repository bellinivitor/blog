<script setup lang="ts">
import { Plus, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { Tag } from '@/types';

const props = defineProps<{
    tags: Tag[];
    defaultSelected?: Tag[];
    defaultNewTags?: string[];
    name: string;
    newName: string;
    id?: string;
}>();

const selected = ref<Tag[]>([...(props.defaultSelected ?? [])]);
const newTags = ref<string[]>([...(props.defaultNewTags ?? [])]);
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

const trimmedQuery = computed(() => query.value.trim());

const canCreate = computed(() => {
    const term = trimmedQuery.value.toLowerCase();

    return (
        term !== '' &&
        !props.tags.some((tag) => tag.name.toLowerCase() === term) &&
        !newTags.value.some((name) => name.toLowerCase() === term)
    );
});

const optionCount = computed(
    () => suggestions.value.length + (canCreate.value ? 1 : 0),
);

function create(): void {
    newTags.value.push(trimmedQuery.value);
    query.value = '';
    highlightedIndex.value = 0;
}

function removeNew(name: string): void {
    newTags.value = newTags.value.filter((item) => item !== name);
}

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
            optionCount.value - 1,
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const tag = suggestions.value[highlightedIndex.value];

        if (tag) {
            select(tag);
        } else if (canCreate.value) {
            create();
        }
    } else if (event.key === 'Escape') {
        isOpen.value = false;
    } else if (event.key === 'Backspace' && query.value === '') {
        if (newTags.value.length) {
            newTags.value.pop();
        } else {
            selected.value.pop();
        }
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
                    <X class="size-3" />
                </button>
                <input type="hidden" :name="`${name}[]`" :value="tag.id" />
            </Badge>

            <Badge
                v-for="tagName in newTags"
                :key="`new-${tagName}`"
                variant="outline"
                class="gap-1 border-dashed pr-1"
            >
                {{ tagName }}
                <span class="text-muted-foreground">(new)</span>
                <button
                    type="button"
                    class="rounded-full p-0.5 hover:bg-foreground/10"
                    :aria-label="`Remove ${tagName}`"
                    @click="removeNew(tagName)"
                >
                    <X class="size-3" />
                </button>
                <input type="hidden" :name="`${newName}[]`" :value="tagName" />
            </Badge>

            <input
                :id="id"
                v-model="query"
                type="text"
                role="combobox"
                autocomplete="off"
                :aria-expanded="isOpen && optionCount > 0"
                class="min-w-32 flex-1 bg-transparent px-1 text-base outline-none placeholder:text-muted-foreground md:text-sm"
                :placeholder="
                    selected.length || newTags.length ? '' : 'Type to add tags'
                "
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
                v-if="canCreate"
                role="option"
                :aria-selected="highlightedIndex === suggestions.length"
                class="flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5"
                :class="highlightedIndex === suggestions.length && 'bg-accent'"
                @mousedown.prevent="create"
                @mouseenter="highlightedIndex = suggestions.length"
            >
                <Plus class="size-4" /> Create “{{ trimmedQuery }}”
            </li>
            <li
                v-if="optionCount === 0"
                class="px-2 py-1.5 text-muted-foreground"
            >
                {{
                    tags.length ? 'No matching tags.' : 'Type to create a tag.'
                }}
            </li>
        </ul>
    </div>
</template>
