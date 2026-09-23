<script setup lang="ts">
import { ref, watch } from 'vue';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { Reading } from '@/types';

const DEBOUNCE_MS = 200;

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    select: [reading: Reading];
}>();

const term = ref('');
const results = ref<Reading[]>([]);
const highlighted = ref(0);
const loading = ref(false);
const failed = ref(false);

let controller: AbortController | null = null;
let timer: ReturnType<typeof setTimeout> | undefined;

async function search(value: string): Promise<void> {
    controller?.abort();
    controller = new AbortController();
    loading.value = true;
    failed.value = false;

    try {
        const response = await fetch(
            ReadingController.search({ query: { search: value || undefined } })
                .url,
            {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            },
        );

        if (!response.ok) {
            throw new Error(`Reading search failed with ${response.status}`);
        }

        results.value = (await response.json()) as Reading[];
        highlighted.value = 0;
    } catch (error) {
        if ((error as Error).name !== 'AbortError') {
            failed.value = true;
            results.value = [];
        }
    } finally {
        loading.value = false;
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        term.value = '';
        void search('');
    } else {
        controller?.abort();
        clearTimeout(timer);
    }
});

watch(term, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => void search(value.trim()), DEBOUNCE_MS);
});

function choose(reading: Reading): void {
    open.value = false;
    emit('select', reading);
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        highlighted.value = Math.min(
            highlighted.value + 1,
            results.value.length - 1,
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlighted.value = Math.max(highlighted.value - 1, 0);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const reading = results.value[highlighted.value];

        if (reading) {
            choose(reading);
        }
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Insert reading</DialogTitle>
                <DialogDescription>
                    The selected text becomes a link to the reading; with no
                    selection, its title is inserted.
                </DialogDescription>
            </DialogHeader>

            <Input
                v-model="term"
                type="search"
                placeholder="Search by title"
                aria-label="Search readings by title"
                aria-controls="reading-picker-results"
                autofocus
                @keydown="onKeydown"
            />

            <ul
                id="reading-picker-results"
                role="listbox"
                aria-label="Readings"
                class="max-h-72 overflow-y-auto"
            >
                <li
                    v-for="(reading, index) in results"
                    :key="reading.id"
                    role="option"
                    :aria-selected="index === highlighted"
                    class="cursor-pointer rounded-md px-3 py-2 text-sm"
                    :class="index === highlighted && 'bg-accent'"
                    @mouseenter="highlighted = index"
                    @mousedown.prevent="choose(reading)"
                >
                    <span class="block font-medium">{{ reading.title }}</span>
                    <span class="block truncate text-muted-foreground">
                        {{ reading.url }}
                    </span>
                </li>
            </ul>

            <p
                v-if="!loading && (failed || results.length === 0)"
                class="text-sm text-muted-foreground"
            >
                {{
                    failed
                        ? 'The readings could not be loaded.'
                        : 'No readings found.'
                }}
            </p>
        </DialogContent>
    </Dialog>
</template>
