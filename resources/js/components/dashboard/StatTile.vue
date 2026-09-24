<script setup lang="ts">
defineProps<{
    label: string;
    value: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1 bg-background p-5">
        <p class="text-sm text-muted-foreground">{{ label }}</p>
        <p class="text-3xl font-semibold tracking-tight tabular-nums">
            {{ value }}
        </p>
        <div class="min-h-5 text-sm text-muted-foreground">
            <slot />
        </div>
    </div>
</template>
