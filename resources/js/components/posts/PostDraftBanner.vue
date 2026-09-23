<script setup lang="ts">
import { History } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    savedAt: string;
}>();

defineEmits<{
    restore: [];
    discard: [];
}>();

function formatDateTime(value: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <div
        role="status"
        class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-dashed px-4 py-3 text-sm"
    >
        <p class="flex items-center gap-2">
            <History class="size-4 text-muted-foreground" />
            Unsaved changes from {{ formatDateTime(savedAt) }} were found in
            this browser.
        </p>
        <div class="flex gap-2">
            <Button size="sm" @click="$emit('restore')">Restore</Button>
            <Button size="sm" variant="ghost" @click="$emit('discard')">
                Discard
            </Button>
        </div>
    </div>
</template>
