<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import { Button } from '@/components/ui/button';
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
</script>

<template>
    <Head title="New post" />

    <div class="max-w-3xl space-y-6 p-4">
        <Heading
            title="New post"
            description="Posts are saved as drafts until you publish them"
        />

        <Form
            v-bind="PostController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <PostFormFields :errors="errors" :tags="tags" />

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
</template>
