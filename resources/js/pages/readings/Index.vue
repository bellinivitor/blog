<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import {
    BookOpen,
    Ellipsis,
    ExternalLink,
    Pencil,
    Plus,
    Quote,
    RotateCcw,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import IndexTabs from '@/components/IndexTabs.vue';
import Pagination from '@/components/Pagination.vue';
import ReadingDialog from '@/components/readings/ReadingDialog.vue';
import SearchField from '@/components/SearchField.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatDate, formatRelative } from '@/lib/adminDates';
import { hostOf } from '@/lib/hostOf';
import type { Paginated, Reading, ReadingFilters, TrashCounts } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Readings',
                href: ReadingController.index(),
            },
        ],
    },
});

const props = defineProps<{
    readings: Paginated<Reading>;
    filters: ReadingFilters;
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
            ReadingController.index.url({
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

const editing = ref<Reading | null>(null);
const isEditing = ref(false);

function openEditor(reading: Reading | null): void {
    editing.value = reading;
    // Let a menu close and hand focus back before the dialog takes it.
    void nextTick(() => {
        isEditing.value = true;
    });
}

const readingToDelete = ref<Reading | null>(null);
const isConfirmingDelete = ref(false);

function confirmDelete(reading: Reading): void {
    readingToDelete.value = reading;
    void nextTick(() => {
        isConfirmingDelete.value = true;
    });
}

function citationsLabel(count: number): string {
    return count === 0
        ? 'Not cited'
        : `Cited in ${count} ${count === 1 ? 'post' : 'posts'}`;
}

const deleteDescription = computed(() => {
    const count = readingToDelete.value?.citations_count ?? 0;

    return count === 0
        ? 'No posts cite it. It moves to the trash, where it can be restored.'
        : `${citationsLabel(count)}. There its link shows as plain text until you restore it from the trash.`;
});

const emptyMessage = computed(() => {
    if (props.filters.search) {
        return {
            title: `No readings match “${props.filters.search}”`,
            body: 'Try another title, or clear the search.',
        };
    }

    return props.filters.trashed
        ? {
              title: 'The trash is empty',
              body: 'Deleted readings wait here so you can restore them.',
          }
        : {
              title: 'No readings yet',
              body: 'Add the books and articles you recommend, then cite them in posts with @.',
          };
});
</script>

<template>
    <Head title="Readings" />

    <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">Readings</h1>
                <p class="text-sm text-muted-foreground">
                    Books and articles you recommend and cite in posts
                </p>
            </div>
            <Button @click="openEditor(null)"> <Plus /> New reading </Button>
        </header>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <IndexTabs
                label="Reading status"
                :tabs="[
                    {
                        key: 'active',
                        label: 'Active',
                        count: counts.active,
                        href: ReadingController.index(),
                        active: !filters.trashed,
                    },
                    {
                        key: 'trashed',
                        label: 'Trash',
                        count: counts.trashed,
                        href: ReadingController.index({
                            query: { trashed: 1 },
                        }),
                        active: filters.trashed,
                    },
                ]"
            />
            <SearchField
                v-model="search"
                placeholder="Search by title"
                class="w-full sm:w-60"
            />
        </div>

        <ul
            v-if="readings.data.length"
            class="divide-y overflow-hidden rounded-xl border"
        >
            <li
                v-for="reading in readings.data"
                :key="reading.id"
                class="relative flex items-center gap-4 px-4 py-4 transition-colors sm:px-5"
                :class="!reading.deleted_at && 'hover:bg-muted/50'"
            >
                <div class="min-w-0 flex-1 space-y-1">
                    <!-- Stretches over the row, so the whole row opens the editor. -->
                    <button
                        v-if="!reading.deleted_at"
                        type="button"
                        class="block max-w-full text-left font-medium outline-none after:absolute after:inset-0 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 focus-visible:after:ring-inset"
                        @click="openEditor(reading)"
                    >
                        {{ reading.title }}
                    </button>
                    <p v-else class="font-medium text-muted-foreground">
                        {{ reading.title }}
                    </p>
                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground"
                    >
                        <!-- Above the stretched button, so the link opens the page. -->
                        <a
                            :href="reading.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="relative z-10 inline-flex items-center gap-1 hover:text-foreground hover:underline"
                        >
                            {{ hostOf(reading.url) ?? reading.url }}
                            <ExternalLink class="size-3" aria-hidden="true" />
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                        <span v-if="reading.deleted_at">
                            Deleted {{ formatRelative(reading.deleted_at) }}
                        </span>
                        <span v-else-if="reading.created_at">
                            Added {{ formatDate(reading.created_at) }}
                        </span>
                        <span class="sm:hidden">
                            {{ citationsLabel(reading.citations_count ?? 0) }}
                        </span>
                    </div>
                </div>

                <span
                    class="hidden shrink-0 items-center gap-1.5 text-sm sm:inline-flex"
                    :class="
                        reading.citations_count
                            ? 'text-foreground'
                            : 'text-muted-foreground/70'
                    "
                >
                    <Quote
                        class="size-3.5 text-muted-foreground"
                        aria-hidden="true"
                    />
                    {{ citationsLabel(reading.citations_count ?? 0) }}
                </span>

                <Button
                    v-if="reading.deleted_at"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link
                        :href="ReadingController.restore(reading)"
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
                            :aria-label="`Actions for “${reading.title}”`"
                        >
                            <Ellipsis />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem @select="openEditor(reading)">
                            <Pencil /> Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="reading.url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <ExternalLink /> Open link
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            variant="destructive"
                            @select="confirmDelete(reading)"
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
            <BookOpen class="size-6 text-muted-foreground" aria-hidden="true" />
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
                <Plus /> New reading
            </Button>
        </div>

        <Pagination :paginator="readings" />

        <ReadingDialog v-model:open="isEditing" :reading="editing" />

        <ConfirmDeleteDialog
            v-if="readingToDelete"
            v-model:open="isConfirmingDelete"
            :title="`Delete “${readingToDelete.title}”?`"
            :description="deleteDescription"
            :form="ReadingController.destroy.form(readingToDelete)"
        />
    </div>
</template>
