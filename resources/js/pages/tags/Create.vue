<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import TagController from '@/actions/App/Http/Controllers/TagController';
import Heading from '@/components/Heading.vue';
import TagFormFields from '@/components/tags/TagFormFields.vue';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tags', href: TagController.index() },
            { title: 'New tag', href: TagController.create() },
        ],
    },
});
</script>

<template>
    <Head title="New tag" />

    <div class="max-w-xl space-y-6 p-4">
        <Heading title="New tag" />

        <Form
            v-bind="TagController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <TagFormFields :errors="errors" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Create</Button>
                <Button variant="ghost" as-child>
                    <Link :href="TagController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
