import { useEventListener } from '@vueuse/core';
import { onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Cmd+S / Ctrl+S submits the form with this id instead of saving the page.
 * Returns the shortcut as the author's keyboard names it, once mounted.
 */
export function useSaveShortcut(formId: string): Ref<string> {
    const label = ref('');

    onMounted(() => {
        label.value = /Mac|iPhone|iPad/.test(navigator.userAgent)
            ? '⌘S'
            : 'Ctrl+S';
    });

    useEventListener('keydown', (event: KeyboardEvent) => {
        if (
            (event.metaKey || event.ctrlKey) &&
            event.key.toLowerCase() === 's'
        ) {
            event.preventDefault();
            (
                document.getElementById(formId) as HTMLFormElement | null
            )?.requestSubmit();
        }
    });

    return label;
}
