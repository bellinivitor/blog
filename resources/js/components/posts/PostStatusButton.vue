<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { EyeOff, Send } from '@lucide/vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import { Button } from '@/components/ui/button';
import type { Post } from '@/types';

defineProps<{
    post: Pick<Post, 'id' | 'status'>;
    size?: 'default' | 'sm';
}>();
</script>

<template>
    <Button
        :variant="post.status === 'published' ? 'outline' : 'default'"
        :size="size ?? 'default'"
        as-child
    >
        <Link
            v-if="post.status === 'published'"
            :href="PostController.unpublish(post)"
            as="button"
            preserve-scroll
        >
            <EyeOff /> Unpublish
        </Link>
        <Link
            v-else
            :href="PostController.publish(post)"
            as="button"
            preserve-scroll
        >
            <Send /> Publish
        </Link>
    </Button>
</template>
