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

    /* Same typefaces as the public post page (resources/css/blog.css). */
    --crepe-base-font-size: 16px;
    --crepe-font-title:
        'Monaspace Xenon', 'Monaspace Neon', ui-monospace, monospace;
    --crepe-font-default:
        'Monaspace Neon', ui-monospace, SFMono-Regular, Menlo, monospace;
    --crepe-font-code:
        'Monaspace Neon', ui-monospace, SFMono-Regular, Menlo, monospace;

    --crepe-shadow-1:
        0px 1px 3px 1px rgba(0, 0, 0, 0.12), 0px 1px 2px 0px rgba(0, 0, 0, 0.2);
    --crepe-shadow-2:
        0px 2px 6px 2px rgba(0, 0, 0, 0.12), 0px 1px 2px 0px rgba(0, 0, 0, 0.2);
}

/*
 * The writing area mirrors the public post column (.blog-prose): a 68ch
 * measure in Monaspace Neon at 16px / 1.8, titles in Xenon at the same sizes,
 * so lines break where they will on the published page. The attribute
 * selector outweighs Crepe's nested `.milkdown .milkdown .ProseMirror` rules.
 */
.markdown-editor .milkdown .ProseMirror[contenteditable] {
    box-sizing: content-box;
    max-width: 68ch;
    min-height: 24rem;
    margin-inline: auto;
    padding: 2rem 3.5rem;
    font-family: var(--crepe-font-default);
    font-size: 1rem;
    line-height: 1.8;
    font-feature-settings:
        'calt' 1,
        'liga' 0;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] p {
    padding: 0;
    line-height: 1.8;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] > * + * {
    margin-top: 1.25em;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] :is(h1, h2, h3, h4) {
    padding: 0;
    font-family: var(--crepe-font-title);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] h1 {
    margin-top: 2.5em;
    font-size: 1.75rem;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] h2 {
    margin-top: 2.5em;
    font-size: 1.375rem;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] h3 {
    margin-top: 2em;
    font-size: 1.125rem;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] h4 {
    margin-top: 1.75em;
    font-size: 1rem;
}

.markdown-editor
    .milkdown
    .ProseMirror[contenteditable]
    :is(h1, h2, h3, h4)
    + * {
    margin-top: 0.75em;
}

.markdown-editor .milkdown .ProseMirror[contenteditable] > :first-child {
    margin-top: 0;
}
</style>
