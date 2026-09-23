<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Reading } from '@/types';

defineProps<{
    errors: Partial<Record<string, string>>;
    reading?: Reading;
}>();
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                :default-value="reading?.title"
                required
                autofocus
                placeholder="Refactoring, by Martin Fowler"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="url">Link</Label>
            <Input
                id="url"
                name="url"
                type="url"
                :default-value="reading?.url"
                required
                placeholder="https://"
            />
            <InputError :message="errors.url" />
        </div>
    </div>
</template>
