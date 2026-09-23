<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import TagController from '@/actions/App/Http/Controllers/TagController';
import Heading from '@/components/Heading.vue';
import TagFormFields from '@/components/tags/TagFormFields.vue';
import { Button } from '@/components/ui/button';
import type { Tag } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tags', href: TagController.index() }],
    },
});

defineProps<{
    tag: Tag;
}>();
</script>

<template>
    <Head :title="`Edit ${tag.name}`" />

    <div class="max-w-xl space-y-6 p-4">
        <Heading :title="`Edit ${tag.name}`" />

        <Form
            v-bind="TagController.update.form(tag)"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <TagFormFields :errors="errors" :tag="tag" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="TagController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
