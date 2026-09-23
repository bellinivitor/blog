<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { PostStatus } from '@/types';

defineProps<{
    status: PostStatus;
}>();
</script>

<template>
    <Badge :variant="status === 'published' ? 'default' : 'secondary'">
        {{ status === 'published' ? 'Published' : 'Draft' }}
    </Badge>
</template>
