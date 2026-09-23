<script setup lang="ts">
import { Crepe } from '@milkdown/crepe';
import '@milkdown/crepe/theme/common/style.css';
import { useHttp } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue';
import { toast } from 'vue-sonner';
import PostImageController from '@/actions/App/Http/Controllers/PostImageController';

const props = defineProps<{
    name: string;
    defaultValue?: string;
    placeholder?: string;
}>();

const root = useTemplateRef<HTMLDivElement>('root');
const markdown = ref(props.defaultValue ?? '');

const http = useHttp<{ image: File | null }, { url: string }>(
    PostImageController.store(),
    { image: null },
);

async function uploadImage(file: File): Promise<string> {
    http.image = file;

    try {
        const response = await http.submit();

        return response.url;
    } catch (error) {
        toast.error(http.errors.image ?? 'The image could not be uploaded.');

        throw error;
    }
}

let crepe: Crepe | null = null;

onMounted(async () => {
    crepe = new Crepe({
        root: root.value,
        defaultValue: markdown.value,
        features: {
            [Crepe.Feature.TopBar]: true,
            [Crepe.Feature.Latex]: false,
            [Crepe.Feature.AI]: false,
        },
        featureConfigs: {
            [Crepe.Feature.ImageBlock]: {
                onUpload: uploadImage,
            },
            [Crepe.Feature.Placeholder]: {
                text: props.placeholder ?? 'Start writing…',
                mode: 'doc',
            },
        },
    });

    crepe.on((listener) => {
        listener.markdownUpdated((_ctx, value) => {
            markdown.value = value;
        });
    });

    await crepe.create();
});

onBeforeUnmount(() => {
    void crepe?.destroy();
});
</script>

<template>
    <div class="markdown-editor overflow-hidden rounded-md border border-input">
        <div ref="root" />
        <input type="hidden" :name="name" :value="markdown" />
    </div>
</template>

<style>
.markdown-editor .milkdown {
    --crepe-color-background: var(--background);
    --crepe-color-on-background: var(--foreground);
    --crepe-color-surface: var(--card);
    --crepe-color-surface-low: var(--muted);
    --crepe-color-on-surface: var(--foreground);
    --crepe-color-on-surface-variant: var(--muted-foreground);
    --crepe-color-outline: var(--border);
    --crepe-color-primary: var(--primary);
    --crepe-color-secondary: var(--secondary);
    --crepe-color-on-secondary: var(--secondary-foreground);
    --crepe-color-inverse: var(--primary);
    --crepe-color-on-inverse: var(--primary-foreground);
    --crepe-color-inline-code: var(--destructive);
    --crepe-color-error: var(--destructive);
    --crepe-color-hover: var(--accent);
    --crepe-color-selected: var(--accent);
    --crepe-color-inline-area: var(--muted);

    --crepe-base-font-size: 16px;
    --crepe-font-title: var(--font-sans);
    --crepe-font-default: var(--font-sans);
    --crepe-font-code:
        ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;

    --crepe-shadow-1:
        0px 1px 3px 1px rgba(0, 0, 0, 0.12), 0px 1px 2px 0px rgba(0, 0, 0, 0.2);
    --crepe-shadow-2:
        0px 2px 6px 2px rgba(0, 0, 0, 0.12), 0px 1px 2px 0px rgba(0, 0, 0, 0.2);
}

.markdown-editor .milkdown .ProseMirror {
    min-height: 24rem;
    padding: 1.5rem 3.5rem;
}
</style>
