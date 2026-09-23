<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import Heading from '@/components/Heading.vue';
import ReadingFormFields from '@/components/readings/ReadingFormFields.vue';
import { Button } from '@/components/ui/button';
import type { Reading } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Readings', href: ReadingController.index() }],
    },
});

defineProps<{
    reading: Reading;
}>();
</script>

<template>
    <Head :title="`Edit reading`" />

    <div class="max-w-xl space-y-6 p-4">
        <Heading :title="`Edit reading`" />

        <Form
            v-bind="ReadingController.update.form(reading)"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <ReadingFormFields :errors="errors" :reading="reading" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="ReadingController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
