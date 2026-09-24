<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import MarkdownEditor from './MarkdownEditor.vue';
import TagInput from './TagInput.vue';
import type { PostDraftFields } from '@/composables/usePostDraft';
import type { Post, Tag } from '@/types';

const props = defineProps<{
    errors: Partial<Record<string, string>>;
    tags: Tag[];
    post?: Post;
    /** Restored local copy; overrides the post's values. */
    draft?: PostDraftFields | null;
}>();

const initial = {
    title: props.draft?.title ?? props.post?.title,
    slug: props.draft?.slug ?? props.post?.slug,
    excerpt: props.draft?.excerpt ?? props.post?.excerpt ?? '',
    content: props.draft?.content ?? props.post?.content,
    tags: props.draft
        ? props.tags.filter((tag) =>
              props.draft?.tag_ids.includes(String(tag.id)),
          )
        : props.post?.tags,
    newTags: props.draft?.new_tags ?? [],
};

const title = ref(initial.title ?? '');

/** Mirrors Str::slug closely enough to preview the address left empty. */
const slugFromTitle = computed(() =>
    title.value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, ''),
);

/** Read on mount: the server render has no window. */
const host = ref('');

onMounted(() => {
    host.value = window.location.host;
});

/** A title is one line: Enter would otherwise add a break or submit. */
function keepOnOneLine(): void {
    title.value = title.value.replace(/\s*\n\s*/g, ' ');
}

function tagError(): string | undefined {
    return (
        props.errors.tag_ids ??
        Object.entries(props.errors).find(
            ([key]) =>
                key.startsWith('tag_ids.') || key.startsWith('new_tags.'),
        )?.[1]
    );
}
</script>

<template>
    <div class="space-y-8">
        <div class="space-y-4">
            <div>
                <label for="title" class="sr-only">Title</label>
                <textarea
                    id="title"
                    v-model="title"
                    name="title"
                    rows="1"
                    required
                    autofocus
                    placeholder="Post title"
                    class="block field-sizing-content w-full resize-none bg-transparent font-['Monaspace_Xenon',ui-monospace,monospace] text-[1.75rem] leading-[1.2] font-bold tracking-tight outline-none placeholder:text-muted-foreground/50 md:text-[2rem]"
                    @keydown.enter.prevent
                    @input="keepOnOneLine"
                />
                <InputError class="mt-2" :message="errors.title" />
            </div>

            <div>
                <label
                    for="slug"
                    class="-ml-2 flex h-8 w-[calc(100%+0.5rem)] items-center rounded-md border border-transparent px-2 font-mono text-xs text-muted-foreground transition-colors focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 hover:border-input"
                >
                    <span class="sr-only">Slug</span>
                    <span aria-hidden="true" class="shrink-0">{{ host }}/</span>
                    <input
                        id="slug"
                        name="slug"
                        :value="initial.slug"
                        :placeholder="slugFromTitle || 'post-address'"
                        aria-describedby="slug-hint"
                        class="min-w-0 flex-1 bg-transparent text-foreground outline-none placeholder:text-muted-foreground/60"
                    />
                </label>
                <p id="slug-hint" class="sr-only">
                    Leave empty to build the address from the title.
                </p>
                <InputError class="mt-1" :message="errors.slug" />
            </div>

            <div>
                <label for="excerpt" class="sr-only">Excerpt</label>
                <textarea
                    id="excerpt"
                    name="excerpt"
                    rows="1"
                    :value="initial.excerpt"
                    placeholder="Add a short summary for listings and search results"
                    class="block field-sizing-content w-full resize-none bg-transparent text-base leading-relaxed text-muted-foreground outline-none placeholder:text-muted-foreground/50 focus:text-foreground md:text-lg"
                />
                <InputError class="mt-1" :message="errors.excerpt" />
            </div>

            <div>
                <label for="tags" class="sr-only">Tags</label>
                <TagInput
                    id="tags"
                    name="tag_ids"
                    new-name="new_tags"
                    :tags="tags"
                    :default-selected="initial.tags"
                    :default-new-tags="initial.newTags"
                />
                <InputError class="mt-1" :message="tagError()" />
            </div>
        </div>

        <div>
            <MarkdownEditor
                name="content"
                :default-value="initial.content"
                placeholder="Start writing… type / for blocks"
            />
            <InputError class="mt-2" :message="errors.content" />
        </div>
    </div>
</template>
