<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MarkdownEditor from './MarkdownEditor.vue';
import TagInput from './TagInput.vue';
import type { PostDraftFields } from '@/composables/usePostDraft';
import type { Post, Tag } from '@/types';

const props = defineProps<{
    errors: Partial<Record<string, string>>;
    tags: Tag[];
    post?: Post;
    /** Restored local copy; overrides the post's values. */
    draft?: PostDraftFields | null;
}>();

const initial = {
    title: props.draft?.title ?? props.post?.title,
    slug: props.draft?.slug ?? props.post?.slug,
    excerpt: props.draft?.excerpt ?? props.post?.excerpt ?? '',
    content: props.draft?.content ?? props.post?.content,
    tags: props.draft
        ? props.tags.filter((tag) =>
              props.draft?.tag_ids.includes(String(tag.id)),
          )
        : props.post?.tags,
    newTags: props.draft?.new_tags ?? [],
};

const textareaClass =
    'w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30';

function tagError(): string | undefined {
    return (
        props.errors.tag_ids ??
        Object.entries(props.errors).find(
            ([key]) =>
                key.startsWith('tag_ids.') || key.startsWith('new_tags.'),
        )?.[1]
    );
}
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="grid content-start gap-2">
                <Label for="title">Title</Label>
                <Input
                    id="title"
                    name="title"
                    :default-value="initial.title"
                    required
                    autofocus
                    placeholder="My new post"
                />
                <InputError :message="errors.title" />
            </div>

            <div class="grid content-start gap-2">
                <Label for="slug">Slug</Label>
                <Input
                    id="slug"
                    name="slug"
                    :default-value="initial.slug"
                    placeholder="Generated from the title when empty"
                />
                <InputError :message="errors.slug" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="excerpt">Excerpt</Label>
            <textarea
                id="excerpt"
                name="excerpt"
                rows="2"
                :class="textareaClass"
                :value="initial.excerpt"
                placeholder="A short summary shown in listings"
            />
            <InputError :message="errors.excerpt" />
        </div>

        <div class="grid gap-2">
            <Label>Content</Label>
            <MarkdownEditor
                name="content"
                :default-value="initial.content"
                placeholder="Start writing… type / for blocks"
            />
            <InputError :message="errors.content" />
        </div>

        <div class="grid gap-2">
            <Label for="tags">Tags</Label>
            <TagInput
                id="tags"
                name="tag_ids"
                new-name="new_tags"
                :tags="tags"
                :default-selected="initial.tags"
                :default-new-tags="initial.newTags"
            />
            <InputError :message="tagError()" />
        </div>
    </div>
</template>
