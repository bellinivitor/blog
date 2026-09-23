<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    useTemplateRef,
    watch,
} from 'vue';
import BlogPostController from '@/actions/App/Http/Controllers/Blog/BlogPostController';
import BlogSearchController from '@/actions/App/Http/Controllers/Blog/BlogSearchController';
import { formatLongDate } from '@/lib/blogDates';

type SearchResult = {
    title: string;
    slug: string;
    published_at: string;
    snippet: string;
};

const MIN_LENGTH = 2;
const DEBOUNCE_MS = 200;

const dialog = useTemplateRef<HTMLDialogElement>('dialog');
const input = useTemplateRef<HTMLInputElement>('input');

const query = ref('');
const results = ref<SearchResult[]>([]);
const highlighted = ref(0);
const loading = ref(false);
const failed = ref(false);

const isMac =
    typeof navigator !== 'undefined' &&
    /Mac|iPhone|iPad/.test(navigator.platform);
const shortcutLabel = isMac ? '⌘K' : 'Ctrl K';

const term = computed(() => query.value.trim());

let timer: ReturnType<typeof setTimeout> | undefined;
let controller: AbortController | null = null;

function open(): void {
    if (!dialog.value?.open) {
        dialog.value?.showModal();
        void nextTick(() => input.value?.select());
    }
}

function close(): void {
    dialog.value?.close();
}

function onShortcut(event: KeyboardEvent): void {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();

        if (dialog.value?.open) {
            close();
        } else {
            open();
        }
    }
}

async function search(value: string): Promise<void> {
    controller?.abort();
    controller = new AbortController();
    loading.value = true;
    failed.value = false;

    try {
        const response = await fetch(
            BlogSearchController.index({ query: { q: value } }).url,
            {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            },
        );

        if (!response.ok) {
            throw new Error(`Search failed with ${response.status}`);
        }

        results.value = (await response.json()) as SearchResult[];
        highlighted.value = 0;
    } catch (error) {
        if ((error as Error).name !== 'AbortError') {
            failed.value = true;
            results.value = [];
        }
    } finally {
        loading.value = false;
    }
}

watch(term, (value) => {
    clearTimeout(timer);

    if (value.length < MIN_LENGTH) {
        controller?.abort();
        results.value = [];
        loading.value = false;

        return;
    }

    timer = setTimeout(() => void search(value), DEBOUNCE_MS);
});

function visit(result: SearchResult): void {
    close();
    router.visit(BlogPostController.show(result.slug));
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        highlighted.value = Math.min(
            highlighted.value + 1,
            results.value.length - 1,
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlighted.value = Math.max(highlighted.value - 1, 0);
    } else if (event.key === 'Enter' && results.value[highlighted.value]) {
        event.preventDefault();
        visit(results.value[highlighted.value]);
    }
}

function onBackdropClick(event: MouseEvent): void {
    if (event.target === dialog.value) {
        close();
    }
}

/** A character without accents, lowercased ("Í" becomes "i"). */
function fold(character: string): string {
    return character.normalize('NFD').replace(/\p{M}/gu, '').toLowerCase();
}

/**
 * Split text around matches of the term, ignoring accents and case, for
 * highlighting. Each character is folded on its own so matches map back to
 * the original text.
 */
function segments(text: string): { text: string; match: boolean }[] {
    const needle = Array.from(term.value).map(fold).join('');

    if (term.value.length < MIN_LENGTH || needle === '') {
        return [{ text, match: false }];
    }

    const characters = Array.from(text);
    let folded = '';
    const originalIndex: number[] = [];

    characters.forEach((character, index) => {
        const piece = fold(character);
        folded += piece;
        originalIndex.push(...Array.from(piece, () => index));
    });

    const parts: { text: string; match: boolean }[] = [];
    let cursor = 0;
    let found = folded.indexOf(needle);

    while (found !== -1) {
        const start = originalIndex[found];
        const end = originalIndex[found + needle.length - 1] + 1;

        if (start > cursor) {
            parts.push({
                text: characters.slice(cursor, start).join(''),
                match: false,
            });
        }

        parts.push({
            text: characters.slice(start, end).join(''),
            match: true,
        });
        cursor = end;
        found = folded.indexOf(needle, found + needle.length);
    }

    if (cursor < characters.length) {
        parts.push({ text: characters.slice(cursor).join(''), match: false });
    }

    return parts;
}

onMounted(() => window.addEventListener('keydown', onShortcut));

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onShortcut);
    clearTimeout(timer);
    controller?.abort();
});
</script>

<template>
    <div class="contents">
        <button type="button" class="search-trigger" @click="open">
            Buscar
            <kbd class="search-kbd">{{ shortcutLabel }}</kbd>
        </button>

        <dialog
            ref="dialog"
            class="search-dialog"
            aria-label="Buscar posts"
            @click="onBackdropClick"
        >
            <div class="search-panel">
                <label class="search-field">
                    <span class="sr-only">Buscar por título ou texto</span>
                    <input
                        ref="input"
                        v-model="query"
                        type="search"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="Buscar por título ou texto"
                        role="combobox"
                        aria-controls="search-results"
                        :aria-expanded="results.length > 0"
                        :aria-activedescendant="
                            results.length
                                ? `search-result-${highlighted}`
                                : undefined
                        "
                        @keydown="onKeydown"
                    />
                    <kbd class="search-kbd">Esc</kbd>
                </label>

                <ul
                    v-if="results.length"
                    id="search-results"
                    role="listbox"
                    class="search-results"
                >
                    <li
                        v-for="(result, index) in results"
                        :id="`search-result-${index}`"
                        :key="result.slug"
                        role="option"
                        :aria-selected="index === highlighted"
                        class="search-result"
                        :class="{
                            'search-result--active': index === highlighted,
                        }"
                        @click="visit(result)"
                        @mousemove="highlighted = index"
                    >
                        <p class="search-result-title">
                            <template
                                v-for="(part, i) in segments(result.title)"
                                :key="i"
                            >
                                <mark v-if="part.match">{{ part.text }}</mark>
                                <template v-else>{{ part.text }}</template>
                            </template>
                        </p>
                        <p class="search-result-snippet">
                            <template
                                v-for="(part, i) in segments(result.snippet)"
                                :key="i"
                            >
                                <mark v-if="part.match">{{ part.text }}</mark>
                                <template v-else>{{ part.text }}</template>
                            </template>
                        </p>
                        <time
                            class="search-result-date"
                            :datetime="result.published_at"
                        >
                            {{ formatLongDate(result.published_at) }}
                        </time>
                    </li>
                </ul>

                <p v-else class="search-status" aria-live="polite">
                    <template v-if="term.length < MIN_LENGTH">
                        Digite pelo menos {{ MIN_LENGTH }} letras para buscar
                        nos títulos e textos dos posts.
                    </template>
                    <template v-else-if="loading">Buscando…</template>
                    <template v-else-if="failed">
                        A busca falhou. Tente de novo em alguns segundos.
                    </template>
                    <template v-else>
                        Nenhum post com “{{ term }}”. Tente outra palavra.
                    </template>
                </p>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.search-trigger {
    display: inline-flex;
    align-items: baseline;
    gap: 1ch;
    color: inherit;
    cursor: pointer;
}

.search-trigger:hover {
    color: var(--ink);
}

.search-trigger:focus-visible {
    outline: 2px solid var(--pen);
    outline-offset: 3px;
    border-radius: 2px;
}

.search-kbd {
    padding: 0 0.5ch;
    border: 1px solid var(--rule);
    border-radius: 4px;
    font-family: var(--font-text);
    font-size: 0.75rem;
    color: var(--graphite);
}

.search-dialog {
    width: min(64ch, calc(100vw - 2rem));
    max-height: min(70vh, 36rem);
    margin: 12vh auto auto;
    padding: 0;
    border: 1px solid var(--rule);
    border-radius: 10px;
    background: var(--paper);
    color: var(--ink);
    box-shadow: 0 24px 48px -12px
        color-mix(in srgb, var(--ink) 25%, transparent);
}

.search-dialog::backdrop {
    background: color-mix(in srgb, var(--paper) 55%, transparent);
    backdrop-filter: blur(2px);
}

.search-panel {
    display: flex;
    flex-direction: column;
    max-height: inherit;
}

.search-field {
    display: flex;
    align-items: center;
    gap: 2ch;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--rule);
}

.search-field input {
    flex: 1;
    min-width: 0;
    background: transparent;
    font-size: 1rem;
    outline: none;
}

.search-field input::placeholder {
    color: var(--graphite);
}

.search-field input::-webkit-search-cancel-button {
    display: none;
}

.search-results {
    overflow-y: auto;
    padding: 0.5rem;
}

.search-result {
    padding: 0.75rem;
    border-radius: 6px;
    cursor: pointer;
}

.search-result--active {
    background: color-mix(in srgb, var(--pen) 9%, transparent);
}

.search-result-title {
    font-family: var(--font-title);
    font-weight: 700;
    line-height: 1.5;
}

.search-result-snippet {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    line-height: 1.6;
    color: var(--graphite);
}

.search-result-date {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: var(--graphite);
}

.search-result mark {
    background: color-mix(in srgb, var(--pen) 22%, transparent);
    color: inherit;
    border-radius: 2px;
}

/* Keyboard hints mean nothing on touch screens. */
@media (max-width: 640px) {
    .search-kbd {
        display: none;
    }

    .search-dialog {
        margin-top: 1rem;
    }
}

.search-status {
    padding: 1.5rem 1.25rem;
    font-size: 0.875rem;
    color: var(--graphite);
}
</style>
