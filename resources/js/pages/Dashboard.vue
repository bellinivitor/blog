<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import { mainNavItems } from '@/lib/navigation';
import { dashboard } from '@/routes';
import type { MostReadPost } from '@/types';

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

defineProps<{
    totalViews: number;
    mostRead: MostReadPost[];
}>();

const numberFormat = new Intl.NumberFormat();

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

        <section class="rounded-xl border p-5">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="font-medium">Reading</h2>
                <p class="text-sm text-muted-foreground">
                    <span class="font-medium text-foreground">{{
                        numberFormat.format(totalViews)
                    }}</span>
                    views on published posts
                </p>
            </div>

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
                No views yet. Visits from readers show up here; your own visits
                while signed in are not counted.
            </p>
        </section>
    </div>
</template>
