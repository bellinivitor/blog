<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TagInput from './TagInput.vue';
import type { Post, Tag } from '@/types';

const props = defineProps<{
    errors: Partial<Record<string, string>>;
    tags: Tag[];
    post?: Post;
}>();

const textareaClass =
    'w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30';

function tagError(): string | undefined {
    return (
        props.errors.tag_ids ??
        Object.entries(props.errors).find(([key]) =>
            key.startsWith('tag_ids.'),
        )?.[1]
    );
}
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                :default-value="post?.title"
                required
                autofocus
                placeholder="My new post"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                name="slug"
                :default-value="post?.slug"
                placeholder="Generated from the title when empty"
            />
            <InputError :message="errors.slug" />
        </div>

        <div class="grid gap-2">
            <Label for="excerpt">Excerpt</Label>
            <textarea
                id="excerpt"
                name="excerpt"
                rows="2"
                :class="textareaClass"
                :value="post?.excerpt ?? ''"
                placeholder="A short summary shown in listings"
            />
            <InputError :message="errors.excerpt" />
        </div>

        <div class="grid gap-2">
            <Label for="content">Content (Markdown)</Label>
            <textarea
                id="content"
                name="content"
                rows="18"
                required
                :class="[textareaClass, 'font-mono']"
                :value="post?.content ?? ''"
                placeholder="# Hello world"
            />
            <InputError :message="errors.content" />
        </div>

        <div class="grid gap-2">
            <Label for="tags">Tags</Label>
            <TagInput
                id="tags"
                name="tag_ids"
                :tags="tags"
                :default-selected="post?.tags"
            />
            <InputError :message="tagError()" />
        </div>
    </div>
</template>
