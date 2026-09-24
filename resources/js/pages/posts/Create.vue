<script setup lang="ts">
import { nextTick, ref, useTemplateRef } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import PostDraftBanner from '@/components/posts/PostDraftBanner.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { usePostDraft } from '@/composables/usePostDraft';
import type { PostDraftFields } from '@/composables/usePostDraft';
import { useSaveShortcut } from '@/composables/useSaveShortcut';
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
const saveShortcut = useSaveShortcut('post-form');
const isSaving = ref(false);

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

    <div class="mx-auto w-full max-w-7xl space-y-6 p-4 md:p-6">
        <PostDraftBanner
            v-if="draft.pendingDraft.value"
            :saved-at="draft.pendingDraft.value.savedAt"
            @restore="restoreDraft"
            @discard="draft.discard()"
        />

        <div
            class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_17rem] xl:gap-10"
        >
            <div ref="formContainer" class="min-w-0">
                <Form
                    id="post-form"
                    :key="formVersion"
                    v-bind="PostController.store.form()"
                    v-slot="{ errors }"
                    @start="isSaving = true"
                    @finish="isSaving = false"
                    @success="onSaved"
                >
                    <PostFormFields
                        :errors="errors"
                        :tags="tags"
                        :draft="restored"
                    />
                </Form>
            </div>

            <aside
                aria-label="Publishing"
                class="space-y-4 rounded-xl border p-4 text-sm xl:sticky xl:top-6"
            >
                <div class="space-y-1">
                    <PostStatusBadge
                        :post="{ status: 'draft', published_at: null }"
                    />
                    <p class="text-muted-foreground">
                        New posts start as drafts. Nothing goes live until you
                        publish it.
                    </p>
                </div>

                <div class="space-y-2">
                    <Button
                        type="submit"
                        form="post-form"
                        class="w-full justify-between"
                        :disabled="isSaving"
                    >
                        <span class="inline-flex items-center gap-2">
                            <Spinner v-if="isSaving" />
                            {{ isSaving ? 'Saving…' : 'Save draft' }}
                        </span>
                        <kbd
                            v-if="saveShortcut"
                            class="font-sans text-xs opacity-60"
                            >{{ saveShortcut }}</kbd
                        >
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="w-full text-muted-foreground"
                        as-child
                    >
                        <Link :href="PostController.index()">Cancel</Link>
                    </Button>
                </div>

                <p class="text-xs text-muted-foreground">
                    Publishing, scheduling and the preview link open up once the
                    draft is saved.
                </p>
            </aside>
        </div>

        <!-- Below xl the panel follows the text, so saving stays one tap away. -->
        <Button
            type="submit"
            form="post-form"
            class="fixed right-4 bottom-4 z-20 shadow-lg xl:hidden"
            :disabled="isSaving"
        >
            <Spinner v-if="isSaving" />
            {{ isSaving ? 'Saving…' : 'Save draft' }}
        </Button>
    </div>
</template>
