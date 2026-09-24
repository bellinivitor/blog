<script setup lang="ts">
import { computed, ref } from 'vue';
import type { DailyViews } from '@/types';

const props = defineProps<{
    days: DailyViews[];
}>();

const numberFormat = new Intl.NumberFormat();
/** English, like the rest of the admin ("Today" sits next to it). */
const dateFormat = new Intl.DateTimeFormat('en', {
    day: 'numeric',
    month: 'short',
});

/** "YYYY-MM-DD" read as a calendar day, not as UTC midnight. */
function formatDay(date: string): string {
    const [year, month, day] = date.split('-').map(Number);

    return dateFormat.format(new Date(year, month - 1, day));
}

const maxViews = computed(() =>
    Math.max(1, ...props.days.map((day) => day.views)),
);

const activeIndex = ref<number | null>(null);
const activeDay = computed(() =>
    activeIndex.value === null ? null : props.days[activeIndex.value],
);

/** Keeps the tooltip inside the chart near both edges. */
const tooltipLeft = computed(() => {
    if (activeIndex.value === null) {
        return '0%';
    }

    const center = ((activeIndex.value + 0.5) / props.days.length) * 100;

    return `clamp(3.5rem, ${center}%, calc(100% - 3.5rem))`;
});

function barHeight(views: number): string {
    return views === 0
        ? '2px'
        : `${Math.max(4, (views / maxViews.value) * 100)}%`;
}
</script>

<template>
    <figure class="m-0">
        <div class="relative">
            <div
                class="pointer-events-none absolute inset-x-0 top-0 flex items-center gap-2 text-xs text-muted-foreground"
                aria-hidden="true"
            >
                <span class="tabular-nums">{{
                    numberFormat.format(maxViews)
                }}</span>
                <span class="flex-1 border-t border-dashed" />
            </div>

            <ol
                class="flex h-36 items-end gap-0.5 pt-5"
                @pointerleave="activeIndex = null"
            >
                <li
                    v-for="(day, index) in days"
                    :key="day.date"
                    class="group flex h-full flex-1 items-end outline-none"
                    tabindex="0"
                    :aria-label="`${formatDay(day.date)}: ${numberFormat.format(day.views)} views`"
                    @pointerenter="activeIndex = index"
                    @focus="activeIndex = index"
                    @blur="activeIndex = null"
                >
                    <span
                        class="w-full rounded-t-[4px] transition-colors group-focus-visible:ring-2 group-focus-visible:ring-ring"
                        :class="
                            day.views === 0
                                ? 'bg-muted-foreground/30'
                                : 'bg-primary/80 group-hover:bg-primary group-focus-visible:bg-primary'
                        "
                        :style="{ height: barHeight(day.views) }"
                    />
                </li>
            </ol>

            <div
                v-if="activeDay"
                class="pointer-events-none absolute top-0 z-10 -translate-x-1/2 rounded-md border bg-popover px-2.5 py-1.5 text-center text-xs whitespace-nowrap shadow-sm"
                :style="{ left: tooltipLeft }"
                role="status"
            >
                <span class="block text-sm font-semibold text-foreground">
                    {{ numberFormat.format(activeDay.views) }} views
                </span>
                <span class="text-muted-foreground">{{
                    formatDay(activeDay.date)
                }}</span>
            </div>
        </div>

        <figcaption
            class="mt-2 flex justify-between text-xs text-muted-foreground"
            aria-hidden="true"
        >
            <span>{{ formatDay(days[0].date) }}</span>
            <span>Today</span>
        </figcaption>

        <!-- sr-only on the table itself does not clip its rows, which then stretch the page. -->
        <div class="sr-only">
            <table>
                <caption>
                    Views per day
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Day</th>
                        <th scope="col">Views</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="day in days" :key="day.date">
                        <td>{{ formatDay(day.date) }}</td>
                        <td>{{ day.views }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </figure>
</template>
