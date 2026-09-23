<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { isScheduled } from '@/lib/postStatus';
import type { Post } from '@/types';

const props = defineProps<{
    post: Pick<Post, 'status' | 'published_at'>;
}>();

const scheduled = computed(() => isScheduled(props.post));
</script>

<template>
    <Badge v-if="scheduled" variant="outline">Scheduled</Badge>
    <Badge
        v-else
        :variant="post.status === 'published' ? 'default' : 'secondary'"
    >
        {{ post.status === 'published' ? 'Published' : 'Draft' }}
    </Badge>
</template>
