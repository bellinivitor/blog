<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import {
    CalendarX,
    Ellipsis,
    ExternalLink,
    Eye,
    EyeOff,
    Heart,
    Pencil,
    PenLine,
    Plus,
    RotateCcw,
    Search,
    Send,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import PostController from '@/actions/App/Http/Controllers/PostController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import Pagination from '@/components/Pagination.vue';
import PostStatusBadge from '@/components/posts/PostStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatDate, formatDateTime, formatRelative } from '@/lib/adminDates';
import { postState } from '@/lib/postStatus';
import type {
    Paginated,
    Post,
    PostCounts,
    PostFilters,
    PostStatus,
    Tag,
} from '@/types';

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

const props = defineProps<{
    posts: Paginated<Post>;
    filters: PostFilters;
    counts: PostCounts;
    tags: Tag[];
}>();

type Tab = 'all' | PostStatus | 'trashed';

const tabs: { key: Tab; label: string }[] = [
    { key: 'all', label: 'All' },
    { key: 'draft', label: 'Drafts' },
    { key: 'published', label: 'Published' },
    { key: 'trashed', label: 'Trash' },
];

const activeTab = computed<Tab>(() => {
    if (props.filters.trashed) {
        return 'trashed';
    }

    return props.filters.status ?? 'all';
});

const search = ref(props.filters.search ?? '');
const hasFilters = computed(
    () => Boolean(props.filters.search) || props.filters.tag_id !== null,
);

type Query = {
    search?: string;
    status?: PostStatus;
    tag_id?: number;
    trashed?: 1;
};

/** The current filters with some replaced, without empty values in the URL. */
function queryWith(changes: Partial<Record<keyof Query, unknown>>): Query {
    const current: Record<string, unknown> = {
        search: props.filters.search,
        status: props.filters.status,
        tag_id: props.filters.tag_id,
        trashed: props.filters.trashed ? 1 : null,
        ...changes,
    };

    return Object.fromEntries(
        Object.entries(current).filter(
            ([, value]) =>
                value !== null && value !== undefined && value !== '',
        ),
    ) as Query;
}

function tabQuery(tab: Tab): Query {
    return queryWith({
        status: tab === 'draft' || tab === 'published' ? tab : null,
        trashed: tab === 'trashed' ? 1 : null,
    });
}

function applyFilters(changes: Partial<Record<keyof Query, unknown>>): void {
    router.get(
        PostController.index.url({ query: queryWith(changes) }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

watchDebounced(
    search,
    (term) => {
        if (term.trim() !== (props.filters.search ?? '')) {
            applyFilters({ search: term.trim() });
        }
    },
    { debounce: 300 },
);

function clearFilters(): void {
    search.value = '';
    applyFilters({ search: null, tag_id: null });
}

const compactNumber = new Intl.NumberFormat(undefined, {
    notation: 'compact',
    maximumFractionDigits: 1,
});
const fullNumber = new Intl.NumberFormat();

function plural(count: number, word: string): string {
    return `${fullNumber.format(count)} ${word}${count === 1 ? '' : 's'}`;
}

/** What the date in each row means depends on where the post stands. */
function dateLine(post: Post): string {
    if (post.deleted_at) {
        return `Deleted ${formatRelative(post.deleted_at)}`;
    }

    const state = postState(post);

    if (state === 'scheduled' && post.published_at) {
        return `Goes live ${formatDateTime(post.published_at)}`;
    }

    if (state === 'published' && post.published_at) {
        return formatDate(post.published_at);
    }

    return post.updated_at
        ? `Edited ${formatRelative(post.updated_at)}`
        : 'Not published yet';
}

const postToDelete = ref<Post | null>(null);
const isConfirmingDelete = ref(false);

function confirmDelete(post: Post): void {
    postToDelete.value = post;
    // Let the menu close and hand focus back before the dialog takes it.
    void nextTick(() => {
        isConfirmingDelete.value = true;
    });
}

const emptyMessage = computed(() => {
    if (hasFilters.value) {
        return {
            title: 'No posts match these filters',
            body: 'Try another title or tag, or clear the filters.',
        };
    }

    return {
        all: {
            title: 'Nothing written yet',
            body: 'Your posts show up here, from the first draft to the published version.',
        },
        draft: {
            title: 'No drafts',
            body: 'Every post you start is kept here until you publish it.',
        },
        published: {
            title: 'Nothing published yet',
            body: 'Publish a draft, or schedule it, and it shows up here.',
        },
        trashed: {
            title: 'The trash is empty',
            body: 'Deleted posts wait here so you can restore them.',
        },
    }[activeTab.value];
});
</script>

<template>
    <Head title="Posts" />

    <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">Posts</h1>
                <p class="text-sm text-muted-foreground">
                    {{ plural(counts.published, 'published post') }} and
                    {{ plural(counts.draft, 'draft') }}
                </p>
            </div>
            <Button as-child>
                <Link :href="PostController.create()"> <Plus /> New post </Link>
            </Button>
        </header>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav
                aria-label="Post status"
                class="-mx-1 flex max-w-full gap-1 overflow-x-auto px-1"
            >
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="PostController.index({ query: tabQuery(tab.key) })"
                    :aria-current="activeTab === tab.key ? 'page' : undefined"
                    preserve-scroll
                    class="inline-flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    :class="
                        activeTab === tab.key
                            ? 'bg-foreground text-background'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                >
                    {{ tab.label }}
                    <span
                        class="text-xs tabular-nums"
                        :class="
                            activeTab === tab.key
                                ? 'text-background/70'
                                : 'text-muted-foreground/80'
                        "
                    >
                        {{ counts[tab.key] }}
                    </span>
                </Link>
            </nav>

            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                <label class="relative min-w-0 flex-1 sm:w-60 sm:flex-none">
                    <span class="sr-only">Search by title</span>
                    <Search
                        class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search by title"
                        class="h-9 w-full rounded-md border border-input bg-transparent pr-3 pl-8 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    />
                </label>
                <select
                    aria-label="Tag"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    :value="filters.tag_id ?? ''"
                    @change="
                        applyFilters({
                            tag_id: ($event.target as HTMLSelectElement).value,
                        })
                    "
                >
                    <option value="">All tags</option>
                    <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                        {{ tag.name }}
                    </option>
                </select>
                <Button
                    v-if="hasFilters"
                    variant="ghost"
                    size="sm"
                    @click="clearFilters"
                >
                    <X /> Clear
                </Button>
            </div>
        </div>

        <ul
            v-if="posts.data.length"
            class="divide-y overflow-hidden rounded-xl border"
        >
            <li
                v-for="post in posts.data"
                :key="post.id"
                class="group relative flex items-center gap-4 px-4 py-4 transition-colors sm:px-5"
                :class="!post.deleted_at && 'hover:bg-muted/50'"
            >
                <div class="min-w-0 flex-1 space-y-1.5">
                    <!-- The title link stretches over the row, so the whole row opens the editor. -->
                    <Link
                        v-if="!post.deleted_at"
                        :href="PostController.edit(post)"
                        class="block font-['Monaspace_Xenon',ui-monospace,monospace] text-[0.9375rem] leading-snug font-bold tracking-tight outline-none after:absolute after:inset-0 after:rounded-[inherit] focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 focus-visible:after:ring-inset"
                    >
                        {{ post.title }}
                    </Link>
                    <p
                        v-else
                        class="font-['Monaspace_Xenon',ui-monospace,monospace] text-[0.9375rem] leading-snug font-bold tracking-tight text-muted-foreground"
                    >
                        {{ post.title }}
                    </p>

                    <p
                        v-if="post.excerpt"
                        class="line-clamp-1 text-sm text-muted-foreground"
                    >
                        {{ post.excerpt }}
                    </p>

                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground"
                    >
                        <PostStatusBadge v-if="!post.deleted_at" :post="post" />
                        <span>{{ dateLine(post) }}</span>
                        <span
                            v-if="post.tags.length"
                            class="flex flex-wrap gap-x-2"
                        >
                            <span v-for="tag in post.tags" :key="tag.id">
                                #{{ tag.slug }}
                            </span>
                        </span>
                        <span
                            class="flex items-center gap-3 tabular-nums sm:hidden"
                        >
                            <span class="inline-flex items-center gap-1">
                                <Eye class="size-3.5" aria-hidden="true" />
                                <span class="sr-only">Views:</span>
                                {{ compactNumber.format(post.views_count) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <Heart class="size-3.5" aria-hidden="true" />
                                <span class="sr-only">Likes:</span>
                                {{ compactNumber.format(post.likes_count) }}
                            </span>
                        </span>
                    </div>
                </div>

                <dl
                    class="hidden shrink-0 grid-cols-[4.5rem_4.5rem] text-right text-sm tabular-nums sm:grid"
                >
                    <div>
                        <dt class="sr-only">Views</dt>
                        <dd
                            class="inline-flex items-center gap-1.5"
                            :class="
                                post.views_count
                                    ? 'text-foreground'
                                    : 'text-muted-foreground/60'
                            "
                        >
                            <Eye
                                class="size-4 text-muted-foreground"
                                aria-hidden="true"
                            />
                            {{ compactNumber.format(post.views_count) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="sr-only">Likes</dt>
                        <dd
                            class="inline-flex items-center gap-1.5"
                            :class="
                                post.likes_count
                                    ? 'text-foreground'
                                    : 'text-muted-foreground/60'
                            "
                        >
                            <Heart
                                class="size-4"
                                :class="
                                    post.likes_count
                                        ? 'fill-rose-500/15 text-rose-600 dark:text-rose-400'
                                        : 'text-muted-foreground'
                                "
                                aria-hidden="true"
                            />
                            {{ compactNumber.format(post.likes_count) }}
                        </dd>
                    </div>
                </dl>

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

                <DropdownMenu v-else>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="relative z-10 size-8 shrink-0 text-muted-foreground"
                            :aria-label="`Actions for “${post.title}”`"
                        >
                            <Ellipsis />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem as-child>
                            <Link :href="PostController.edit(post)">
                                <Pencil /> Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="PostController.preview(post).url"
                                target="_blank"
                                rel="noopener"
                            >
                                <Eye /> Preview
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-if="postState(post) === 'published'"
                            as-child
                        >
                            <a
                                :href="BlogPostController.show(post.slug).url"
                                target="_blank"
                                rel="noopener"
                            >
                                <ExternalLink /> View on blog
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            v-if="post.status === 'draft'"
                            @select="
                                router.visit(PostController.publish(post), {
                                    preserveScroll: true,
                                })
                            "
                        >
                            <Send /> Publish now
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-else
                            @select="
                                router.visit(PostController.unpublish(post), {
                                    preserveScroll: true,
                                })
                            "
                        >
                            <template v-if="postState(post) === 'scheduled'">
                                <CalendarX /> Unschedule
                            </template>
                            <template v-else><EyeOff /> Unpublish</template>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            variant="destructive"
                            @select="confirmDelete(post)"
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
            <PenLine class="size-6 text-muted-foreground" aria-hidden="true" />
            <div class="space-y-1">
                <p class="font-medium">{{ emptyMessage.title }}</p>
                <p class="max-w-sm text-sm text-muted-foreground">
                    {{ emptyMessage.body }}
                </p>
            </div>
            <Button
                v-if="hasFilters"
                variant="outline"
                size="sm"
                @click="clearFilters"
            >
                <X /> Clear filters
            </Button>
            <Button
                v-else-if="activeTab === 'all' || activeTab === 'draft'"
                size="sm"
                as-child
            >
                <Link :href="PostController.create()">
                    <Plus /> Write a post
                </Link>
            </Button>
        </div>

        <Pagination :paginator="posts" />

        <ConfirmDeleteDialog
            v-if="postToDelete"
            v-model:open="isConfirmingDelete"
            :title="`Delete “${postToDelete.title}”?`"
            description="The post leaves the blog right away and moves to the trash, where it can be restored."
            :form="PostController.destroy.form(postToDelete)"
        />
    </div>
</template>
