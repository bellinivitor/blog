<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import PostStatusButton from '@/components/posts/PostStatusButton.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Paginated, Post, PostFilters, Tag } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Posts',
                href: PostController.index(),
            },
        ],
    },
});

defineProps<{
    posts: Paginated<Post>;
    filters: PostFilters;
    tags: Tag[];
}>();

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30';

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString() : '—';
}
</script>

<template>
    <Head title="Posts" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading title="Posts" description="Write and publish your posts" />
            <Button as-child>
                <Link :href="PostController.create()"> <Plus /> New post </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-1 rounded-lg bg-muted p-1 text-sm">
                <Link
                    :href="PostController.index()"
                    class="rounded-md px-3 py-1"
                    :class="!filters.trashed && 'bg-background shadow-sm'"
                >
                    Active
                </Link>
                <Link
                    :href="PostController.index({ query: { trashed: 1 } })"
                    class="rounded-md px-3 py-1"
                    :class="filters.trashed && 'bg-background shadow-sm'"
                >
                    Trash
                </Link>
            </div>

            <Form
                v-bind="PostController.index.form()"
                :options="{ preserveState: true, replace: true }"
                class="flex flex-wrap gap-2"
            >
                <input
                    v-if="filters.trashed"
                    type="hidden"
                    name="trashed"
                    value="1"
                />
                <Input
                    name="search"
                    type="search"
                    :default-value="filters.search ?? ''"
                    placeholder="Search by title"
                    class="w-56"
                />
                <select
                    name="status"
                    aria-label="Status"
                    :class="selectClass"
                    :value="filters.status ?? ''"
                >
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
                <select
                    name="tag_id"
                    aria-label="Tag"
                    :class="selectClass"
                    :value="filters.tag_id ?? ''"
                >
                    <option value="">All tags</option>
                    <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                        {{ tag.name }}
                    </option>
                </select>
                <Button type="submit" variant="secondary">Filter</Button>
            </Form>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tags</th>
                        <th class="px-4 py-3 font-medium">Published</th>
                        <th class="px-4 py-3 text-right font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="post in posts.data"
                        :key="post.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ post.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ post.slug }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <PostStatusBadge :status="post.status" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    variant="outline"
                                >
                                    {{ tag.name }}
                                </Badge>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ formatDate(post.published_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="post.deleted_at"
                                    variant="outline"
                                    size="sm"
                                    as-child
                                >
                                    <Link
                                        :href="PostController.restore(post)"
                                        as="button"
                                        preserve-scroll
                                    >
                                        <RotateCcw /> Restore
                                    </Link>
                                </Button>
                                <template v-else>
                                    <PostStatusButton :post="post" size="sm" />
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="PostController.edit(post)">
                                            <Pencil /> Edit
                                        </Link>
                                    </Button>
                                    <ConfirmDeleteDialog
                                        :title="`Delete “${post.title}”?`"
                                        description="The post will be moved to the trash and can be restored later."
                                        :form="
                                            PostController.destroy.form(post)
                                        "
                                    >
                                        <Button variant="outline" size="sm">
                                            <Trash2 /> Delete
                                        </Button>
                                    </ConfirmDeleteDialog>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="posts.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            {{
                                filters.trashed
                                    ? 'The trash is empty.'
                                    : 'No posts found.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="posts" />
    </div>
</template>
