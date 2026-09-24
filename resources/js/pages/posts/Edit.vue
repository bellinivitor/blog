<script setup lang="ts">
import { nextTick, ref, useTemplateRef } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import { ExternalLink, Eye, Heart } from '@lucide/vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import PostController from '@/actions/App/Http/Controllers/PostController';
import PostDraftBanner from '@/components/posts/PostDraftBanner.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import PostPreviewLink from '@/components/posts/PostPreviewLink.vue';
import PostScheduleForm from '@/components/posts/PostScheduleForm.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import PostStatusButton from '@/components/posts/PostStatusButton.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { usePostDraft } from '@/composables/usePostDraft';
import type { PostDraftFields } from '@/composables/usePostDraft';
import { useSaveShortcut } from '@/composables/useSaveShortcut';
import { formatDate, formatDateTime, formatRelative } from '@/lib/adminDates';
import { postState } from '@/lib/postStatus';
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

const formContainer = useTemplateRef<HTMLElement>('formContainer');
const draft = usePostDraft(
    String(props.post.id),
    props.post.updated_at,
    formContainer,
);
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

const numberFormat = new Intl.NumberFormat();
</script>

<template>
    <Head :title="`Edit ${post.title}`" />

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
                    :key="`${post.updated_at ?? post.id}-${formVersion}`"
                    v-bind="PostController.update.form(post)"
                    :options="{ preserveScroll: true }"
                    v-slot="{ errors }"
                    @start="isSaving = true"
                    @finish="isSaving = false"
                    @success="onSaved"
                >
                    <PostFormFields
                        :errors="errors"
                        :tags="tags"
                        :post="post"
                        :draft="restored"
                    />
                </Form>
            </div>

            <aside
                aria-label="Publishing"
                class="divide-y rounded-xl border text-sm xl:sticky xl:top-6"
            >
                <section class="space-y-4 p-4">
                    <div class="space-y-1">
                        <PostStatusBadge :post="post" />
                        <p class="text-muted-foreground">
                            <template
                                v-if="
                                    postState(post) === 'scheduled' &&
                                    post.published_at
                                "
                            >
                                Goes live
                                {{ formatDateTime(post.published_at) }}
                            </template>
                            <template
                                v-else-if="
                                    postState(post) === 'published' &&
                                    post.published_at
                                "
                            >
                                Live since {{ formatDate(post.published_at) }}
                            </template>
                            <template v-else>
                                Only you can see it until you publish.
                            </template>
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Button
                            type="submit"
                            form="post-form"
                            :variant="
                                post.status === 'draft' ? 'outline' : 'default'
                            "
                            class="w-full justify-between"
                            :disabled="isSaving"
                        >
                            <span class="inline-flex items-center gap-2">
                                <Spinner v-if="isSaving" />
                                {{ isSaving ? 'Saving…' : 'Save changes' }}
                            </span>
                            <kbd
                                v-if="saveShortcut"
                                class="font-sans text-xs opacity-60"
                                >{{ saveShortcut }}</kbd
                            >
                        </Button>
                        <p
                            v-if="post.updated_at"
                            class="text-center text-xs text-muted-foreground"
                            :title="formatDateTime(post.updated_at)"
                        >
                            Saved {{ formatRelative(post.updated_at) }}
                        </p>
                    </div>
                </section>

                <section class="space-y-2 p-4">
                    <PostStatusButton :post="post" class="w-full" />
                    <PostScheduleForm
                        v-if="post.status === 'draft'"
                        :post="post"
                    />
                    <div class="flex gap-2 pt-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="flex-1 text-muted-foreground"
                            as-child
                        >
                            <a
                                :href="PostController.preview(post).url"
                                target="_blank"
                                rel="noopener"
                            >
                                <Eye /> Preview
                            </a>
                        </Button>
                        <Button
                            v-if="postState(post) === 'published'"
                            variant="ghost"
                            size="sm"
                            class="flex-1 text-muted-foreground"
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
                    </div>
                </section>

                <section
                    v-if="postState(post) === 'published'"
                    class="grid grid-cols-2 gap-4 p-4"
                >
                    <div>
                        <p
                            class="flex items-center gap-1.5 text-muted-foreground"
                        >
                            <Eye class="size-4" aria-hidden="true" /> Views
                        </p>
                        <p
                            class="text-2xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ numberFormat.format(post.views_count) }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="flex items-center gap-1.5 text-muted-foreground"
                        >
                            <Heart
                                class="size-4"
                                :class="
                                    post.likes_count
                                        ? 'fill-rose-500/15 text-rose-600 dark:text-rose-400'
                                        : ''
                                "
                                aria-hidden="true"
                            />
                            Likes
                        </p>
                        <p
                            class="text-2xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ numberFormat.format(post.likes_count) }}
                        </p>
                    </div>
                </section>

                <section class="p-4">
                    <PostPreviewLink :post="post" />
                </section>
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
            {{ isSaving ? 'Saving…' : 'Save changes' }}
        </Button>
    </div>
</template>
