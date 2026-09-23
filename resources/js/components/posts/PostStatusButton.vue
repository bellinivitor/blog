<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarX, EyeOff, Send } from '@lucide/vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import { Button } from '@/components/ui/button';
import { isScheduled } from '@/lib/postStatus';
import type { Post } from '@/types';

defineProps<{
    post: Pick<Post, 'id' | 'status' | 'published_at'>;
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
            <template v-if="isScheduled(post)"
                ><CalendarX /> Unschedule</template
            >
            <template v-else><EyeOff /> Unpublish</template>
        </Link>
        <Link
            v-else
            :href="PostController.publish(post)"
            as="button"
            preserve-scroll
        >
            <Send /> Publish now
        </Link>
    </Button>
</template>
