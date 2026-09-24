<script setup lang="ts">
import { nextTick, ref, useTemplateRef } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ExternalLink, Eye } from '@lucide/vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostDraftBanner from '@/components/posts/PostDraftBanner.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import PostPreviewLink from '@/components/posts/PostPreviewLink.vue';
import PostScheduleForm from '@/components/posts/PostScheduleForm.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import PostStatusButton from '@/components/posts/PostStatusButton.vue';
import { Button } from '@/components/ui/button';
import { usePostDraft } from '@/composables/usePostDraft';
import type { PostDraftFields } from '@/composables/usePostDraft';
import { isScheduled } from '@/lib/postStatus';
import type { Post, Tag } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Posts', href: PostController.index() }],
    },
});

const props = defineProps<{
    post: Post;
    tags: Tag[];
}>();

function formatDateTime(value: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

const formContainer = useTemplateRef<HTMLElement>('formContainer');
const draft = usePostDraft(
    String(props.post.id),
    props.post.updated_at,
    formContainer,
);
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
    <Head :title="`Edit ${post.title}`" />

    <div class="space-y-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-2">
                <Heading :title="post.title" variant="small" />
                <div class="flex items-center gap-2">
                    <PostStatusBadge :post="post" />
                    <span
                        v-if="isScheduled(post) && post.published_at"
                        class="text-sm text-muted-foreground"
                    >
                        Goes live {{ formatDateTime(post.published_at) }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-start justify-end gap-2">
                <Button variant="outline" as-child>
                    <a
                        :href="PostController.preview(post).url"
                        target="_blank"
                        rel="noopener"
                    >
                        <Eye /> Preview
                    </a>
                </Button>
                <Button
                    v-if="post.status === 'published' && !isScheduled(post)"
                    variant="outline"
                    as-child
                >
                    <a
                        :href="BlogPostController.show(post.slug).url"
                        target="_blank"
                        rel="noopener"
                    >
                        <ExternalLink /> View post
                    </a>
                </Button>
                <PostScheduleForm v-if="post.status === 'draft'" :post="post" />
                <PostStatusButton :post="post" />
            </div>
        </div>

        <PostPreviewLink :post="post" />

        <div ref="formContainer" class="space-y-6">
            <PostDraftBanner
                v-if="draft.pendingDraft.value"
                :saved-at="draft.pendingDraft.value.savedAt"
                @restore="restoreDraft"
                @discard="draft.discard()"
            />

            <Form
                :key="`${post.updated_at ?? post.id}-${formVersion}`"
                v-bind="PostController.update.form(post)"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="onSaved"
            >
                <PostFormFields
                    :errors="errors"
                    :tags="tags"
                    :post="post"
                    :draft="restored"
                />

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="processing">Save</Button>
                    <Button variant="ghost" as-child>
                        <Link :href="PostController.index()"
                            >Back to posts</Link
                        >
                    </Button>
                </div>
            </Form>
        </div>
    </div>
</template>
