<script setup lang="ts">
import { computed } from 'vue';
import { postState, postStateLabels } from '@/lib/postStatus';
import type { Post } from '@/types';

const props = defineProps<{
    post: Pick<Post, 'status' | 'published_at'>;
}>();

const state = computed(() => postState(props.post));
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 text-xs font-medium"
        :class="{
            'text-emerald-700 dark:text-emerald-400': state === 'published',
            'text-amber-700 dark:text-amber-400': state === 'scheduled',
            'text-muted-foreground': state === 'draft',
        }"
    >
        <!-- Shape as well as color: filled is live, half is waiting, hollow is not out. -->
        <span
            aria-hidden="true"
            class="size-2 shrink-0 rounded-full border-[1.5px] border-current"
            :class="{
                'bg-current': state === 'published',
                'bg-[linear-gradient(90deg,currentColor_50%,transparent_50%)]':
                    state === 'scheduled',
            }"
        />
        {{ postStateLabels[state] }}
    </span>
</template>
