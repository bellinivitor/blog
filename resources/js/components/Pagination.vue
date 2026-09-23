<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineProps<{
    paginator: Paginated<unknown>;
}>();
</script>

<template>
    <nav
        v-if="paginator.meta.last_page > 1"
        class="flex items-center justify-between gap-4 text-sm"
        aria-label="Pagination"
    >
        <p class="text-muted-foreground">
            Showing {{ paginator.meta.from }}–{{ paginator.meta.to }} of
            {{ paginator.meta.total }}
        </p>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="!paginator.links.prev"
                :as-child="!!paginator.links.prev"
            >
                <Link
                    v-if="paginator.links.prev"
                    :href="paginator.links.prev"
                    preserve-scroll
                >
                    <ChevronLeft /> Previous
                </Link>
                <template v-else><ChevronLeft /> Previous</template>
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="!paginator.links.next"
                :as-child="!!paginator.links.next"
            >
                <Link
                    v-if="paginator.links.next"
                    :href="paginator.links.next"
                    preserve-scroll
                >
                    Next <ChevronRight />
                </Link>
                <template v-else>Next <ChevronRight /></template>
            </Button>
        </div>
    </nav>
</template>
