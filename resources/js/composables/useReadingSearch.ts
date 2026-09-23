import { onBeforeUnmount, ref } from 'vue';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import type { Reading } from '@/types';

const DEBOUNCE_MS = 150;

/**
 * Debounced search of readings by title for the post editor (the picker
 * dialog and the @ autocomplete). A newer search cancels the one in flight.
 */
export function useReadingSearch() {
    const results = ref<Reading[]>([]);
    const loading = ref(false);
    const failed = ref(false);

    let controller: AbortController | null = null;
    let timer: ReturnType<typeof setTimeout> | undefined;

    async function run(term: string): Promise<void> {
        controller?.abort();
        controller = new AbortController();
        loading.value = true;
        failed.value = false;

        try {
            const response = await fetch(
                ReadingController.search({
                    query: { search: term || undefined },
                }).url,
                {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                },
            );

            if (!response.ok) {
                throw new Error(
                    `Reading search failed with ${response.status}`,
                );
            }

            results.value = (await response.json()) as Reading[];
        } catch (error) {
            if ((error as Error).name !== 'AbortError') {
                failed.value = true;
                results.value = [];
            }
        } finally {
            loading.value = false;
        }
    }

    function search(term: string): void {
        clearTimeout(timer);
        timer = setTimeout(() => void run(term.trim()), DEBOUNCE_MS);
    }

    function cancel(): void {
        clearTimeout(timer);
        controller?.abort();
    }

    onBeforeUnmount(cancel);

    return { results, loading, failed, search, cancel };
}
