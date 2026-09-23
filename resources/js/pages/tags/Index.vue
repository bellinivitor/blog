<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import TagController from '@/actions/App/Http/Controllers/TagController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Paginated, Tag, TagFilters } from '@/types';

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

defineProps<{
    tags: Paginated<Tag>;
    filters: TagFilters;
}>();
</script>

<template>
    <Head title="Tags" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading title="Tags" description="Organize your posts by topic" />
            <Button as-child>
                <Link :href="TagController.create()"> <Plus /> New tag </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-1 rounded-lg bg-muted p-1 text-sm">
                <Link
                    :href="TagController.index()"
                    class="rounded-md px-3 py-1"
                    :class="!filters.trashed && 'bg-background shadow-sm'"
                >
                    Active
                </Link>
                <Link
                    :href="TagController.index({ query: { trashed: 1 } })"
                    class="rounded-md px-3 py-1"
                    :class="filters.trashed && 'bg-background shadow-sm'"
                >
                    Trash
                </Link>
            </div>

            <Form
                v-bind="TagController.index.form()"
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
                    placeholder="Search by name"
                    class="w-56"
                />
                <Button type="submit" variant="secondary">Search</Button>
            </Form>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 text-right font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tag in tags.data"
                        :key="tag.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-3 font-medium">{{ tag.name }}</td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ tag.slug }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
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
                                <template v-else>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="TagController.edit(tag)">
                                            <Pencil /> Edit
                                        </Link>
                                    </Button>
                                    <ConfirmDeleteDialog
                                        :title="`Delete “${tag.name}”?`"
                                        description="The tag will be moved to the trash and can be restored later."
                                        :form="TagController.destroy.form(tag)"
                                    >
                                        <Button variant="outline" size="sm">
                                            <Trash2 /> Delete
                                        </Button>
                                    </ConfirmDeleteDialog>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="tags.data.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            {{
                                filters.trashed
                                    ? 'The trash is empty.'
                                    : 'No tags found.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="tags" />
    </div>
</template>
