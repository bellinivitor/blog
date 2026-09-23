<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Paginated, Reading, ReadingFilters } from '@/types';

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

defineProps<{
    readings: Paginated<Reading>;
    filters: ReadingFilters;
}>();
</script>

<template>
    <Head title="Readings" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Readings"
                description="Books and articles you recommend, all as links"
            />
            <Button as-child>
                <Link :href="ReadingController.create()">
                    <Plus /> New reading
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-1 rounded-lg bg-muted p-1 text-sm">
                <Link
                    :href="ReadingController.index()"
                    class="rounded-md px-3 py-1"
                    :class="!filters.trashed && 'bg-background shadow-sm'"
                >
                    Active
                </Link>
                <Link
                    :href="ReadingController.index({ query: { trashed: 1 } })"
                    class="rounded-md px-3 py-1"
                    :class="filters.trashed && 'bg-background shadow-sm'"
                >
                    Trash
                </Link>
            </div>

            <Form
                v-bind="ReadingController.index.form()"
                :options="{ preserveState: true, replace: true }"
                class="flex gap-2"
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
                <Button type="submit" variant="secondary">Search</Button>
            </Form>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Link</th>
                        <th class="px-4 py-3 text-right font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="reading in readings.data"
                        :key="reading.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ reading.title }}
                        </td>
                        <td class="max-w-80 truncate px-4 py-3">
                            <a
                                :href="reading.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-muted-foreground hover:text-foreground hover:underline"
                            >
                                {{ reading.url }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="reading.deleted_at"
                                    variant="outline"
                                    size="sm"
                                    as-child
                                >
                                    <Link
                                        :href="
                                            ReadingController.restore(reading)
                                        "
                                        as="button"
                                        preserve-scroll
                                    >
                                        <RotateCcw /> Restore
                                    </Link>
                                </Button>
                                <template v-else>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                ReadingController.edit(reading)
                                            "
                                        >
                                            <Pencil /> Edit
                                        </Link>
                                    </Button>
                                    <ConfirmDeleteDialog
                                        :title="`Delete “${reading.title}”?`"
                                        description="The reading will be moved to the trash and can be restored later."
                                        :form="
                                            ReadingController.destroy.form(
                                                reading,
                                            )
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
                    <tr v-if="readings.data.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            {{
                                filters.trashed
                                    ? 'The trash is empty.'
                                    : 'No readings found.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="readings" />
    </div>
</template>
