<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { RouteDefinition } from '@/wayfinder';

defineProps<{
    label: string;
    tabs: {
        key: string;
        label: string;
        count: number;
        href: RouteDefinition<'get'>;
        active: boolean;
    }[];
}>();
</script>

<template>
    <nav
        :aria-label="label"
        class="-mx-1 flex max-w-full gap-1 overflow-x-auto px-1"
    >
        <Link
            v-for="tab in tabs"
            :key="tab.key"
            :href="tab.href"
            :aria-current="tab.active ? 'page' : undefined"
            preserve-scroll
            class="inline-flex shrink-0 items-center gap-2 rounded-md px-3 py-1.5 text-sm transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
            :class="
                tab.active
                    ? 'bg-foreground text-background'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            "
        >
            {{ tab.label }}
            <span
                class="text-xs tabular-nums"
                :class="
                    tab.active
                        ? 'text-background/70'
                        : 'text-muted-foreground/80'
                "
            >
                {{ tab.count }}
            </span>
        </Link>
    </nav>
</template>
