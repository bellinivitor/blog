<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import {
    Ellipsis,
    ExternalLink,
    Pencil,
    Plus,
    RotateCcw,
    Tags,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import BlogTagController from '@/actions/App/Http/Controllers/Blog/BlogTagController';
import TagController from '@/actions/App/Http/Controllers/TagController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import IndexTabs from '@/components/IndexTabs.vue';
import Pagination from '@/components/Pagination.vue';
import SearchField from '@/components/SearchField.vue';
import TagDialog from '@/components/tags/TagDialog.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Paginated, Tag, TagFilters, TrashCounts } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tags',
                href: TagController.index(),
            },
        ],
    },
});

const props = defineProps<{
    tags: Paginated<Tag>;
    filters: TagFilters;
    counts: TrashCounts;
}>();

const search = ref(props.filters.search ?? '');

watchDebounced(
    search,
    (term) => {
        if (term.trim() === (props.filters.search ?? '')) {
            return;
        }

        router.get(
            TagController.index.url({
                query: {
                    ...(term.trim() ? { search: term.trim() } : {}),
                    ...(props.filters.trashed ? { trashed: 1 } : {}),
                },
            }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    },
    { debounce: 300 },
);

const editing = ref<Tag | null>(null);
const isEditing = ref(false);

function openEditor(tag: Tag | null): void {
    editing.value = tag;
    // Let a menu close and hand focus back before the dialog takes it.
    void nextTick(() => {
        isEditing.value = true;
    });
}

const tagToDelete = ref<Tag | null>(null);
const isConfirmingDelete = ref(false);

function confirmDelete(tag: Tag): void {
    tagToDelete.value = tag;
    void nextTick(() => {
        isConfirmingDelete.value = true;
    });
}

function postsLabel(count: number): string {
    return `${count} ${count === 1 ? 'post' : 'posts'}`;
}

const deleteDescription = computed(() => {
    const count = tagToDelete.value?.posts_count ?? 0;

    return count === 0
        ? 'No posts use it. It moves to the trash, where it can be restored.'
        : `Its ${postsLabel(count)} stop showing it until you restore it from the trash.`;
});

const emptyMessage = computed(() => {
    if (props.filters.search) {
        return {
            title: `No tags match “${props.filters.search}”`,
            body: 'Try another name, or clear the search.',
        };
    }

    return props.filters.trashed
        ? {
              title: 'The trash is empty',
              body: 'Deleted tags wait here so you can restore them.',
          }
        : {
              title: 'No tags yet',
              body: 'Create tags here, or type new ones straight into a post.',
          };
});
</script>

<template>
    <Head title="Tags" />

    <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">Tags</h1>
                <p class="text-sm text-muted-foreground">
                    Topics that group your posts, each with a page on the blog
                </p>
            </div>
            <Button @click="openEditor(null)"> <Plus /> New tag </Button>
        </header>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <IndexTabs
                label="Tag status"
                :tabs="[
                    {
                        key: 'active',
                        label: 'Active',
                        count: counts.active,
                        href: TagController.index(),
                        active: !filters.trashed,
                    },
                    {
                        key: 'trashed',
                        label: 'Trash',
                        count: counts.trashed,
                        href: TagController.index({ query: { trashed: 1 } }),
                        active: filters.trashed,
                    },
                ]"
            />
            <SearchField
                v-model="search"
                placeholder="Search by name"
                class="w-full sm:w-60"
            />
        </div>

        <!-- Two columns of short rows; the gap-px grid draws the dividers. -->
        <ul
            v-if="tags.data.length"
            class="grid gap-px overflow-hidden rounded-xl border bg-border md:grid-cols-2"
        >
            <li
                v-for="tag in tags.data"
                :key="tag.id"
                class="relative flex items-center gap-3 bg-background px-4 py-3.5 transition-colors md:[&:last-child:nth-child(odd)]:col-span-2"
                :class="!tag.deleted_at && 'hover:bg-muted/50'"
            >
                <div class="min-w-0 flex-1">
                    <!-- Stretches over the row, so the whole row opens the editor. -->
                    <button
                        v-if="!tag.deleted_at"
                        type="button"
                        class="block max-w-full truncate text-left font-medium outline-none after:absolute after:inset-0 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 focus-visible:after:ring-inset"
                        @click="openEditor(tag)"
                    >
                        {{ tag.name }}
                    </button>
                    <p
                        v-else
                        class="truncate font-medium text-muted-foreground"
                    >
                        {{ tag.name }}
                    </p>
                    <p class="truncate font-mono text-xs text-muted-foreground">
                        #{{ tag.slug }}
                    </p>
                </div>

                <span
                    class="shrink-0 text-sm tabular-nums"
                    :class="
                        tag.posts_count
                            ? 'text-foreground'
                            : 'text-muted-foreground/70'
                    "
                >
                    {{ postsLabel(tag.posts_count ?? 0) }}
                </span>

                <Button
                    v-if="tag.deleted_at"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link
                        :href="TagController.restore(tag)"
                        as="button"
                        preserve-scroll
                    >
                        <RotateCcw /> Restore
                    </Link>
                </Button>

                <DropdownMenu v-else>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="relative z-10 size-8 shrink-0 text-muted-foreground"
                            :aria-label="`Actions for “${tag.name}”`"
                        >
                            <Ellipsis />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem @select="openEditor(tag)">
                            <Pencil /> Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="BlogTagController.show(tag.slug).url"
                                target="_blank"
                                rel="noopener"
                            >
                                <ExternalLink /> View on blog
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            variant="destructive"
                            @select="confirmDelete(tag)"
                        >
                            <Trash2 /> Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </li>
        </ul>

        <div
            v-else
            class="flex flex-col items-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center"
        >
            <Tags class="size-6 text-muted-foreground" aria-hidden="true" />
            <div class="space-y-1">
                <p class="font-medium">{{ emptyMessage.title }}</p>
                <p class="max-w-sm text-sm text-muted-foreground">
                    {{ emptyMessage.body }}
                </p>
            </div>
            <Button
                v-if="filters.search"
                variant="outline"
                size="sm"
                @click="search = ''"
            >
                <X /> Clear search
            </Button>
            <Button
                v-else-if="!filters.trashed"
                size="sm"
                @click="openEditor(null)"
            >
                <Plus /> New tag
            </Button>
        </div>

        <Pagination :paginator="tags" />

        <TagDialog v-model:open="isEditing" :tag="editing" />

        <ConfirmDeleteDialog
            v-if="tagToDelete"
            v-model:open="isConfirmingDelete"
            :title="`Delete “${tagToDelete.name}”?`"
            :description="deleteDescription"
            :form="TagController.destroy.form(tagToDelete)"
        />
    </div>
</template>
