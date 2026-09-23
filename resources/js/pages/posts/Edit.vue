<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import PostScheduleForm from '@/components/posts/PostScheduleForm.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import PostStatusButton from '@/components/posts/PostStatusButton.vue';
import { Button } from '@/components/ui/button';
import { isScheduled } from '@/lib/postStatus';
import type { Post, Tag } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Posts', href: PostController.index() }],
    },
});

defineProps<{
    post: Post;
    tags: Tag[];
}>();

function formatDateTime(value: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
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
                <PostScheduleForm v-if="post.status === 'draft'" :post="post" />
                <PostStatusButton :post="post" />
            </div>
        </div>

        <Form
            :key="post.updated_at ?? post.id"
            v-bind="PostController.update.form(post)"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <PostFormFields :errors="errors" :tags="tags" :post="post" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="PostController.index()">Back to posts</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
