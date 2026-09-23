<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CalendarClock } from '@lucide/vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { Post } from '@/types';

defineProps<{
    post: Pick<Post, 'id'>;
}>();

/** datetime-local has no time zone: send the reader's local time as UTC ISO. */
function toUtc(data: Record<string, unknown>): Record<string, string> {
    const value = String(data.published_at ?? '');

    return { published_at: value ? new Date(value).toISOString() : '' };
}
</script>

<template>
    <Form
        v-bind="PostController.publish.form(post)"
        :transform="toUtc"
        :options="{ preserveScroll: true }"
        class="flex flex-wrap items-start gap-2"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-1">
            <label for="published_at" class="sr-only">Publish at</label>
            <input
                id="published_at"
                name="published_at"
                type="datetime-local"
                required
                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
            />
            <InputError :message="errors.published_at" />
        </div>
        <Button type="submit" variant="outline" :disabled="processing">
            <CalendarClock /> Schedule
        </Button>
    </Form>
</template>
