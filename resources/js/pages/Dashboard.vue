<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { mainNavItems } from '@/lib/navigation';
import { dashboard } from '@/routes';

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
    </div>
</template>
