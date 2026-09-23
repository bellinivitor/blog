<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowDownRight, ArrowRight, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import DailyViewsChart from '@/components/dashboard/DailyViewsChart.vue';
import StatTile from '@/components/dashboard/StatTile.vue';
import Heading from '@/components/Heading.vue';
import { mainNavItems } from '@/lib/navigation';
import { dashboard } from '@/routes';
import type { DailyViews, MostReadPost } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    periodDays: number;
    viewsInPeriod: number;
    viewsInPreviousPeriod: number;
    dailyViews: DailyViews[];
    totalViews: number;
    publishedCount: number;
    draftCount: number;
    scheduledCount: number;
    lastPublishedAt: string | null;
    mostRead: MostReadPost[];
}>();

const numberFormat = new Intl.NumberFormat();
const compactFormat = new Intl.NumberFormat(undefined, {
    notation: 'compact',
    maximumFractionDigits: 1,
});

/** Change against the previous period; null when there is nothing to compare. */
const viewsChange = computed(() => {
    if (props.viewsInPreviousPeriod === 0) {
        return null;
    }

    return Math.round(
        ((props.viewsInPeriod - props.viewsInPreviousPeriod) /
            props.viewsInPreviousPeriod) *
            100,
    );
});

const daysSinceLastPost = computed(() => {
    if (!props.lastPublishedAt) {
        return null;
    }

    const elapsed = Date.now() - new Date(props.lastPublishedAt).getTime();

    return Math.max(0, Math.floor(elapsed / 86_400_000));
});

const lastPostLabel = computed(() => {
    const days = daysSinceLastPost.value;

    if (days === null) {
        return '—';
    }

    return days === 0 ? 'Today' : `${days} ${days === 1 ? 'day' : 'days'} ago`;
});

const draftsSummary = computed(() =>
    [
        `${props.draftCount} ${props.draftCount === 1 ? 'draft' : 'drafts'}`,
        props.scheduledCount ? `${props.scheduledCount} scheduled` : null,
    ]
        .filter(Boolean)
        .join(' · '),
);

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const shortcuts = [
    {
        item: mainNavItems.find((item) => item.title === 'Posts')!,
        description: 'Write drafts, publish and organize your posts.',
    },
    {
        item: mainNavItems.find((item) => item.title === 'Tags')!,
        description: 'Manage the topics used to group your posts.',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            :title="`Hello, ${firstName}`"
            description="What are you writing today?"
        />

        <div class="grid gap-4 md:grid-cols-2">
            <Link
                v-for="{ item, description } in shortcuts"
                :key="item.title"
                :href="item.href"
                class="group flex items-start gap-4 rounded-xl border p-5 transition-colors hover:bg-accent"
            >
                <component
                    :is="item.icon"
                    class="mt-0.5 size-5 text-muted-foreground"
                />
                <div class="flex-1 space-y-1">
                    <p class="font-medium">{{ item.title }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <ArrowRight
                    class="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5"
                />
            </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatTile
                :label="`Views, last ${periodDays} days`"
                :value="compactFormat.format(viewsInPeriod)"
            >
                <span
                    v-if="viewsChange !== null"
                    class="inline-flex items-center gap-1"
                >
                    <span
                        class="inline-flex items-center font-medium"
                        :class="
                            viewsChange >= 0
                                ? 'text-emerald-700 dark:text-emerald-400'
                                : 'text-red-700 dark:text-red-400'
                        "
                    >
                        <component
                            :is="
                                viewsChange >= 0 ? ArrowUpRight : ArrowDownRight
                            "
                            class="size-4"
                            aria-hidden="true"
                        />
                        {{ viewsChange >= 0 ? '+' : '' }}{{ viewsChange }}%
                    </span>
                    vs previous {{ periodDays }} days
                </span>
                <span v-else-if="viewsInPeriod">
                    No views in the previous {{ periodDays }} days
                </span>
            </StatTile>

            <StatTile
                label="Views, all time"
                :value="compactFormat.format(totalViews)"
            >
                on published posts
            </StatTile>

            <StatTile
                label="Published posts"
                :value="numberFormat.format(publishedCount)"
            >
                {{ draftsSummary }}
            </StatTile>

            <StatTile label="Last post" :value="lastPostLabel">
                <template v-if="daysSinceLastPost === null">
                    Nothing published yet
                </template>
                <template v-else>
                    published
                    {{
                        new Date(lastPublishedAt!).toLocaleDateString(
                            undefined,
                            { day: 'numeric', month: 'short', year: 'numeric' },
                        )
                    }}
                </template>
            </StatTile>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <section class="rounded-xl border p-5">
                <h2 class="font-medium">Views per day</h2>
                <p class="text-sm text-muted-foreground">
                    Last {{ periodDays }} days. Your own visits while signed in
                    are not counted.
                </p>
                <div class="mt-4">
                    <DailyViewsChart :days="dailyViews" />
                </div>
            </section>

            <section class="rounded-xl border p-5">
                <h2 class="font-medium">Most read</h2>
                <p class="text-sm text-muted-foreground">All time</p>

                <ol v-if="mostRead.length" class="mt-4 divide-y">
                    <li
                        v-for="post in mostRead"
                        :key="post.id"
                        class="flex items-baseline justify-between gap-4 py-2.5"
                    >
                        <Link
                            :href="PostController.edit(post.id)"
                            class="truncate text-sm hover:underline"
                        >
                            {{ post.title }}
                        </Link>
                        <span
                            class="shrink-0 text-sm text-muted-foreground tabular-nums"
                        >
                            {{ numberFormat.format(post.views_count) }}
                        </span>
                    </li>
                </ol>
                <p v-else class="mt-4 text-sm text-muted-foreground">
                    No views yet. Visits from readers show up here.
                </p>
            </section>
        </div>
    </div>
</template>
