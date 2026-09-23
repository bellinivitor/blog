<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Tag } from '@/types';

defineProps<{
    errors: Partial<Record<string, string>>;
    tag?: Tag;
}>();
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input
                id="name"
                name="name"
                :default-value="tag?.name"
                required
                autofocus
                placeholder="Laravel"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                name="slug"
                :default-value="tag?.slug"
                placeholder="Generated from the name when empty"
            />
            <InputError :message="errors.slug" />
        </div>
    </div>
</template>
