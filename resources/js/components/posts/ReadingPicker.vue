<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useReadingSearch } from '@/composables/useReadingSearch';
import type { Reading } from '@/types';

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    select: [reading: Reading];
}>();

const term = ref('');
const highlighted = ref(0);
const { results, loading, failed, search, cancel } = useReadingSearch();

watch(results, () => {
    highlighted.value = 0;
});

watch(open, (isOpen) => {
    if (isOpen) {
        term.value = '';
        search('');
    } else {
        cancel();
    }
});

watch(term, search);

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
