<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CalendarClock } from '@lucide/vue';
import { ref } from 'vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Post } from '@/types';

defineProps<{
    post: Pick<Post, 'id'>;
}>();

const open = ref(false);

/** datetime-local has no time zone: send the reader's local time as UTC ISO. */
function toUtc(data: Record<string, unknown>): Record<string, string> {
    const value = String(data.published_at ?? '');

    return { published_at: value ? new Date(value).toISOString() : '' };
}

/** Tomorrow at 9:00 local time, as a datetime-local value. */
function suggestedTime(): string {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    date.setHours(9, 0, 0, 0);

    const pad = (value: number) => String(value).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T09:00`;
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" class="w-full">
                <CalendarClock /> Schedule
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <Form
                v-bind="PostController.publish.form(post)"
                :transform="toUtc"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>Schedule this post</DialogTitle>
                    <DialogDescription>
                        It stays hidden from the blog until this moment, then
                        goes live on its own. A past date backdates it.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <label for="published_at" class="text-sm font-medium">
                        Publish at
                    </label>
                    <input
                        id="published_at"
                        name="published_at"
                        type="datetime-local"
                        required
                        :value="suggestedTime()"
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    />
                    <InputError :message="errors.published_at" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        <CalendarClock /> Schedule
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
