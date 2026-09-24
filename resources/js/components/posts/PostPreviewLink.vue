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
    <div class="space-y-3 text-sm">
        <div class="space-y-1">
            <h2 class="font-medium">Preview link</h2>
            <p class="text-muted-foreground">
                {{
                    post.preview_url
                        ? 'Anyone with this link can read the post, even before it is published.'
                        : 'Share the post before it goes live. Only people with the link can read it.'
                }}
            </p>
        </div>

        <template v-if="post.preview_url">
            <div class="flex gap-1.5">
                <input
                    :value="post.preview_url"
                    readonly
                    aria-label="Preview link"
                    class="h-8 min-w-0 flex-1 rounded-md border border-input bg-muted/40 px-2 font-mono text-xs text-muted-foreground outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    @focus="($event.target as HTMLInputElement).select()"
                />
                <Button
                    type="button"
                    size="icon"
                    variant="outline"
                    class="size-8"
                    :aria-label="copied ? 'Copied' : 'Copy link'"
                    @click="copy(post.preview_url)"
                >
                    <Check v-if="copied" class="text-emerald-600" />
                    <Copy v-else />
                </Button>
            </div>
            <Button
                size="sm"
                variant="ghost"
                class="-ml-2 text-muted-foreground"
                as-child
            >
                <Link
                    :href="PostPreviewLinkController.destroy(post)"
                    as="button"
                    preserve-scroll
                >
                    <Link2Off /> Disable link
                </Link>
            </Button>
        </template>
        <Button v-else size="sm" variant="outline" class="w-full" as-child>
            <Link
                :href="PostPreviewLinkController.store(post)"
                as="button"
                preserve-scroll
            >
                <Link2 /> Enable preview link
            </Link>
        </Button>
    </div>
</template>
