<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
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
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { hostOf } from '@/lib/hostOf';
import type { Reading } from '@/types';

/** The reading to edit, or null to create one. */
const props = defineProps<{
    reading: Reading | null;
}>();

const open = defineModel<boolean>('open', { required: true });

const title = ref('');
const url = ref('');

watch(open, (isOpen) => {
    if (isOpen) {
        title.value = props.reading?.title ?? '';
        url.value = props.reading?.url ?? '';
    }
});

const host = computed(() => hostOf(url.value));

const description = computed(() => {
    if (!props.reading) {
        return 'A book or article you recommend. Cite it in a post with @ or the book button in the editor.';
    }

    const count = props.reading.citations_count ?? 0;

    return count === 0
        ? 'No posts cite this reading yet.'
        : `Cited in ${count} ${count === 1 ? 'post' : 'posts'}. A new link updates all of them.`;
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <Form
                :key="reading?.id ?? 'new'"
                v-bind="
                    reading
                        ? ReadingController.update.form(reading)
                        : ReadingController.store.form()
                "
                :options="{ preserveScroll: true, preserveState: 'errors' }"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{ reading ? 'Edit reading' : 'New reading' }}
                    </DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="reading-title">Title</Label>
                        <Input
                            id="reading-title"
                            v-model="title"
                            name="title"
                            required
                            autofocus
                            placeholder="Refactoring, by Martin Fowler"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="reading-url">Link</Label>
                        <Input
                            id="reading-url"
                            v-model="url"
                            name="url"
                            type="url"
                            required
                            placeholder="https://"
                            aria-describedby="reading-url-host"
                        />
                        <p
                            id="reading-url-host"
                            class="min-h-4 text-xs text-muted-foreground"
                        >
                            <template v-if="host">Opens on {{ host }}</template>
                        </p>
                        <InputError :message="errors.url" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ reading ? 'Save changes' : 'Create reading' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
