<script setup lang="ts">
defineProps<{
    label: string;
    value: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1 rounded-xl border p-5">
        <p class="text-sm text-muted-foreground">{{ label }}</p>
        <p class="text-3xl font-semibold tracking-tight">{{ value }}</p>
        <div class="min-h-5 text-sm text-muted-foreground">
            <slot />
        </div>
    </div>
</template>
