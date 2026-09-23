<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { Check, Copy, Link2, Link2Off } from '@lucide/vue';
import PostPreviewLinkController from '@/actions/App/Http/Controllers/PostPreviewLinkController';
import { Button } from '@/components/ui/button';
import type { Post } from '@/types';

defineProps<{
    post: Pick<Post, 'id' | 'preview_url'>;
}>();

const { copy, copied } = useClipboard();
</script>

<template>
    <div
        class="flex flex-wrap items-center gap-3 rounded-lg border border-dashed px-4 py-3 text-sm"
    >
        <template v-if="post.preview_url">
            <Link2 class="size-4 shrink-0 text-muted-foreground" />
            <span class="text-muted-foreground"
                >Anyone with this link can read the post:</span
            >
            <input
                :value="post.preview_url"
                readonly
                aria-label="Preview link"
                class="h-8 min-w-0 flex-1 basis-64 rounded-md border border-input bg-transparent px-2 font-mono text-xs dark:bg-input/30"
                @focus="($event.target as HTMLInputElement).select()"
            />
            <div class="flex gap-2">
                <Button
                    size="sm"
                    variant="outline"
                    @click="copy(post.preview_url)"
                >
                    <Check v-if="copied" /><Copy v-else />
                    {{ copied ? 'Copied' : 'Copy' }}
                </Button>
                <Button size="sm" variant="ghost" as-child>
                    <Link
                        :href="PostPreviewLinkController.destroy(post)"
                        as="button"
                        preserve-scroll
                    >
                        <Link2Off /> Disable
                    </Link>
                </Button>
            </div>
        </template>
        <template v-else>
            <Link2Off class="size-4 shrink-0 text-muted-foreground" />
            <span class="flex-1 text-muted-foreground">
                The preview is private. Enable a link to share it with anyone.
            </span>
            <Button size="sm" variant="outline" as-child>
                <Link
                    :href="PostPreviewLinkController.store(post)"
                    as="button"
                    preserve-scroll
                >
                    <Link2 /> Enable preview link
                </Link>
            </Button>
        </template>
    </div>
</template>
