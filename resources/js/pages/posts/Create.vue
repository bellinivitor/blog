<script setup lang="ts">
import { nextTick, ref, useTemplateRef } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostDraftBanner from '@/components/posts/PostDraftBanner.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import { Button } from '@/components/ui/button';
import { usePostDraft } from '@/composables/usePostDraft';
import type { PostDraftFields } from '@/composables/usePostDraft';
import type { Tag } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Posts', href: PostController.index() },
            { title: 'New post', href: PostController.create() },
        ],
    },
});

defineProps<{
    tags: Tag[];
}>();

const formContainer = useTemplateRef<HTMLElement>('formContainer');
const draft = usePostDraft('new', null, formContainer);
const restored = ref<PostDraftFields | null>(null);
const formVersion = ref(0);

function restoreDraft(): void {
    restored.value = draft.takePendingDraft();
    formVersion.value++;
}

function onSaved(): void {
    draft.clear();
    restored.value = null;
    void nextTick(() => draft.rebase());
}
</script>

<template>
    <Head title="New post" />

    <div class="space-y-6 p-4">
        <Heading
            title="New post"
            description="Posts are saved as drafts until you publish them"
        />

        <div ref="formContainer" class="space-y-6">
            <PostDraftBanner
                v-if="draft.pendingDraft.value"
                :saved-at="draft.pendingDraft.value.savedAt"
                @restore="restoreDraft"
                @discard="draft.discard()"
            />

            <Form
                :key="formVersion"
                v-bind="PostController.store.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="onSaved"
            >
                <PostFormFields
                    :errors="errors"
                    :tags="tags"
                    :draft="restored"
                />

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="processing">
                        Save draft
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link :href="PostController.index()">Cancel</Link>
                    </Button>
                </div>
            </Form>
        </div>
    </div>
</template>
