<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import PostStatusButton from '@/components/posts/PostStatusButton.vue';
import { Button } from '@/components/ui/button';
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
</script>

<template>
    <Head :title="`Edit ${post.title}`" />

    <div class="max-w-3xl space-y-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-2">
                <Heading :title="post.title" variant="small" />
                <PostStatusBadge :status="post.status" />
            </div>
            <PostStatusButton :post="post" />
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
