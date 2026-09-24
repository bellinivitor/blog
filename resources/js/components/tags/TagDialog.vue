<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import TagController from '@/actions/App/Http/Controllers/TagController';
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
import { slugify } from '@/lib/slugify';
import type { Tag } from '@/types';

/** The tag to edit, or null to create one. */
const props = defineProps<{
    tag: Tag | null;
}>();

const open = defineModel<boolean>('open', { required: true });

const name = ref('');
const slug = ref('');
const host = ref('');

onMounted(() => {
    host.value = window.location.host;
});

watch(open, (isOpen) => {
    if (isOpen) {
        name.value = props.tag?.name ?? '';
        slug.value = props.tag?.slug ?? '';
    }
});

const slugFromName = computed(() => slugify(name.value));

const usage = computed(() => {
    const count = props.tag?.posts_count ?? 0;

    return count === 0
        ? 'No posts use this tag yet.'
        : `Used by ${count} ${count === 1 ? 'post' : 'posts'}. Renaming it updates all of them.`;
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <Form
                :key="tag?.id ?? 'new'"
                v-bind="
                    tag
                        ? TagController.update.form(tag)
                        : TagController.store.form()
                "
                :options="{ preserveScroll: true, preserveState: 'errors' }"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>{{
                        tag ? 'Edit tag' : 'New tag'
                    }}</DialogTitle>
                    <DialogDescription>
                        {{
                            tag
                                ? usage
                                : 'Tags group posts by topic, each with its own page on the blog.'
                        }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="tag-name">Name</Label>
                        <Input
                            id="tag-name"
                            v-model="name"
                            name="name"
                            required
                            autofocus
                            placeholder="Laravel"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="tag-slug">Address</Label>
                        <div
                            class="flex h-9 items-center rounded-md border border-input px-3 font-mono text-xs text-muted-foreground shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 dark:bg-input/30"
                        >
                            <span aria-hidden="true" class="shrink-0"
                                >{{ host }}/tags/</span
                            >
                            <input
                                id="tag-slug"
                                v-model="slug"
                                name="slug"
                                :placeholder="slugFromName || 'topic'"
                                class="min-w-0 flex-1 bg-transparent text-foreground outline-none placeholder:text-muted-foreground/60"
                            />
                        </div>
                        <InputError :message="errors.slug" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ tag ? 'Save changes' : 'Create tag' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
