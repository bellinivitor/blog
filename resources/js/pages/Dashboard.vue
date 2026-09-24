<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    CalendarClock,
    Eye,
    Heart,
    PenLine,
    Plus,
} from '@lucide/vue';
import { computed } from 'vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import DailyViewsChart from '@/components/dashboard/DailyViewsChart.vue';
import StatTile from '@/components/dashboard/StatTile.vue';
import { Button } from '@/components/ui/button';
import { formatDateTime, formatRelative } from '@/lib/adminDates';
import { dashboard } from '@/routes';
import type { DailyViews, MostReadPost, PostSummary } from '@/types';

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
    totalLikes: number;
    publishedCount: number;
    draftCount: number;
    scheduledCount: number;
    lastPublishedAt: string | null;
    mostRead: MostReadPost[];
    drafts: PostSummary[];
    scheduled: PostSummary[];
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

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const lastPostSentence = computed(() => {
    if (!props.lastPublishedAt) {
        return 'Nothing published yet. Your first post is one draft away.';
    }

    const elapsed = Date.now() - new Date(props.lastPublishedAt).getTime();

    return elapsed < 86_400_000
        ? 'Your last post went out today.'
        : `Your last post went out ${formatRelative(props.lastPublishedAt)}.`;
});

const currentDraft = computed(() => props.drafts[0] ?? null);
const otherDrafts = computed(() => props.drafts.slice(1));

const draftsSummary = computed(() =>
    [
        `${props.draftCount} ${props.draftCount === 1 ? 'draft' : 'drafts'}`,
        props.scheduledCount ? `${props.scheduledCount} scheduled` : null,
    ]
        .filter(Boolean)
        .join(', '),
);

const topViews = computed(() =>
    Math.max(1, ...props.mostRead.map((post) => post.views_count)),
);

const titleFont = "font-['Monaspace_Xenon',ui-monospace,monospace]";
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Hello, {{ firstName }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ lastPostSentence }}
                </p>
            </div>
            <Button as-child>
                <Link :href="PostController.create()"> <Plus /> New post </Link>
            </Button>
        </header>

        <!-- The writing desk: the draft to pick up, and what else is waiting. -->
        <section
            aria-labelledby="desk-heading"
            class="grid overflow-hidden rounded-xl border lg:grid-cols-[minmax(0,1fr)_20rem]"
        >
            <div class="flex flex-col gap-5 p-6 md:p-8">
                <h2
                    id="desk-heading"
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <PenLine class="size-4" aria-hidden="true" />
                    {{
                        currentDraft
                            ? 'Pick up where you left off'
                            : 'No drafts in progress'
                    }}
                </h2>

                <template v-if="currentDraft">
                    <div class="space-y-2">
                        <Link
                            :href="PostController.edit(currentDraft.id)"
                            class="block text-2xl leading-tight font-bold tracking-tight text-balance outline-none hover:underline focus-visible:underline md:text-[1.75rem]"
                            :class="titleFont"
                        >
                            {{ currentDraft.title }}
                        </Link>
                        <p
                            v-if="currentDraft.excerpt"
                            class="line-clamp-2 max-w-prose text-muted-foreground"
                        >
                            {{ currentDraft.excerpt }}
                        </p>
                    </div>
                    <div class="mt-auto flex flex-wrap items-center gap-4">
                        <Button as-child>
                            <Link :href="PostController.edit(currentDraft.id)">
                                Continue writing
                            </Link>
                        </Button>
                        <span
                            v-if="currentDraft.updated_at"
                            class="text-sm text-muted-foreground"
                            :title="formatDateTime(currentDraft.updated_at)"
                        >
                            Edited {{ formatRelative(currentDraft.updated_at) }}
                        </span>
                    </div>
                </template>

                <template v-else>
                    <p class="max-w-prose text-muted-foreground">
                        Everything you started is either out or scheduled. Start
                        a new post and it waits here until you publish it.
                    </p>
                    <div class="mt-auto">
                        <Button variant="outline" as-child>
                            <Link :href="PostController.create()">
                                <Plus /> Start a post
                            </Link>
                        </Button>
                    </div>
                </template>
            </div>

            <div
                class="flex flex-col gap-6 border-t bg-muted/40 p-6 text-sm lg:border-t-0 lg:border-l"
            >
                <div class="space-y-3">
                    <h3 class="font-medium">Scheduled</h3>
                    <ul v-if="scheduled.length" class="space-y-3">
                        <li v-for="post in scheduled" :key="post.id">
                            <Link
                                :href="PostController.edit(post.id)"
                                class="block truncate hover:underline"
                            >
                                {{ post.title }}
                            </Link>
                            <span
                                v-if="post.published_at"
                                class="flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-400"
                            >
                                <CalendarClock
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                                Goes live
                                {{ formatDateTime(post.published_at) }}
                            </span>
                        </li>
                    </ul>
                    <p v-else class="text-muted-foreground">
                        Nothing scheduled. Schedule a draft from its editor to
                        publish it later.
                    </p>
                </div>

                <div v-if="otherDrafts.length" class="space-y-3">
                    <h3 class="font-medium">Other drafts</h3>
                    <ul class="space-y-3">
                        <li v-for="post in otherDrafts" :key="post.id">
                            <Link
                                :href="PostController.edit(post.id)"
                                class="block truncate hover:underline"
                            >
                                {{ post.title }}
                            </Link>
                            <span
                                v-if="post.updated_at"
                                class="text-xs text-muted-foreground"
                            >
                                Edited {{ formatRelative(post.updated_at) }}
                            </span>
                        </li>
                    </ul>
                    <Link
                        v-if="draftCount > drafts.length"
                        :href="
                            PostController.index({
                                query: { status: 'draft' },
                            })
                        "
                        class="inline-block text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                    >
                        See all {{ draftCount }} drafts
                    </Link>
                </div>
            </div>
        </section>

        <!-- One strip, split into cells: the numbers read as a set. -->
        <section
            aria-label="Blog numbers"
            class="grid gap-px overflow-hidden rounded-xl border bg-border sm:grid-cols-2 xl:grid-cols-4"
        >
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
                label="Likes, all time"
                :value="compactFormat.format(totalLikes)"
            >
                <template v-if="totalViews && totalLikes">
                    1 like every
                    {{
                        numberFormat.format(Math.round(totalViews / totalLikes))
                    }}
                    views
                </template>
                <template v-else>hearts left at the end of posts</template>
            </StatTile>

            <StatTile
                label="Published posts"
                :value="numberFormat.format(publishedCount)"
            >
                {{ draftsSummary }}
            </StatTile>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
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
                <div class="flex items-baseline justify-between gap-4">
                    <h2 class="font-medium">Most read</h2>
                    <span
                        class="flex gap-4 text-muted-foreground"
                        aria-hidden="true"
                    >
                        <span class="flex w-8 justify-end" title="Views"
                            ><Eye class="size-3.5"
                        /></span>
                        <span class="flex w-8 justify-end" title="Likes"
                            ><Heart class="size-3.5"
                        /></span>
                    </span>
                </div>
                <p class="text-sm text-muted-foreground">All time</p>

                <ol v-if="mostRead.length" class="mt-4 space-y-4">
                    <li
                        v-for="(post, index) in mostRead"
                        :key="post.id"
                        class="grid grid-cols-[1.25rem_minmax(0,1fr)_auto] items-baseline gap-x-3"
                    >
                        <span
                            class="text-sm text-muted-foreground tabular-nums"
                        >
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 space-y-1.5">
                            <Link
                                :href="PostController.edit(post.id)"
                                class="line-clamp-2 text-sm leading-snug font-bold hover:underline"
                                :class="titleFont"
                            >
                                {{ post.title }}
                            </Link>
                            <!-- Share of the top post's views. -->
                            <span
                                class="block h-1 rounded-full bg-muted"
                                aria-hidden="true"
                            >
                                <span
                                    class="block h-full rounded-full bg-foreground/70"
                                    :style="{
                                        width: `${(post.views_count / topViews) * 100}%`,
                                    }"
                                />
                            </span>
                        </div>
                        <span class="flex gap-4 text-sm tabular-nums">
                            <span class="w-8 text-right">
                                <span class="sr-only">Views:</span>
                                {{ compactFormat.format(post.views_count) }}
                            </span>
                            <span
                                class="w-8 text-right"
                                :class="
                                    post.likes_count
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : 'text-muted-foreground'
                                "
                            >
                                <span class="sr-only">Likes:</span>
                                {{ compactFormat.format(post.likes_count) }}
                            </span>
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
