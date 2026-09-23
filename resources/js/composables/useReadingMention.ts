import type { EditorView } from '@milkdown/kit/prose/view';
import { onBeforeUnmount, ref, watch } from 'vue';
import { useReadingSearch } from '@/composables/useReadingSearch';
import type { Reading } from '@/types';

/** "@" at the start of the text or after a space, then the typed term. */
const MENTION_PATTERN = /(?:^|\s)@([^\s@]{0,40})$/u;

/** How far back from the cursor to look for the "@". */
const LOOKBEHIND = 48;

export type ReadingMention = {
    /** Document range of "@term", replaced by the chosen reading. */
    from: number;
    to: number;
    term: string;
    /** Viewport position of the "@", where the suggestions open. */
    position: { left: number; top: number };
};

/**
 * IDE-like autocomplete of readings in the post editor: typing "@" opens
 * suggestions that narrow as you type; arrows choose, Enter or Tab inserts
 * the reading as a [title](leitura:ID) link, Esc dismisses.
 */
export function useReadingMention(
    onInsert: (
        view: EditorView,
        mention: ReadingMention,
        reading: Reading,
    ) => void,
) {
    const mention = ref<ReadingMention | null>(null);
    const highlighted = ref(0);
    const { results, loading, search, cancel } = useReadingSearch();

    let view: EditorView | null = null;
    /** Start of an "@" dismissed with Esc, so it stays closed while typing on. */
    let dismissedFrom: number | null = null;

    watch(results, () => {
        highlighted.value = 0;
    });

    function close(): void {
        mention.value = null;
        cancel();
    }

    function update(): void {
        if (!view || !view.hasFocus()) {
            close();

            return;
        }

        const { selection } = view.state;
        const { $from } = selection;

        if (!selection.empty || $from.parent.type.spec.code) {
            close();

            return;
        }

        const before = $from.parent.textBetween(
            Math.max(0, $from.parentOffset - LOOKBEHIND),
            $from.parentOffset,
            undefined,
            '￼',
        );
        const match = before.match(MENTION_PATTERN);

        if (!match) {
            dismissedFrom = null;
            close();

            return;
        }

        const term = match[1];
        const from = selection.from - term.length - 1;

        if (from === dismissedFrom) {
            return;
        }

        const coords = view.coordsAtPos(from);
        const termChanged = mention.value?.term !== term;

        mention.value = {
            from,
            to: selection.from,
            term,
            position: { left: coords.left, top: coords.bottom },
        };

        if (termChanged) {
            search(term);
        }
    }

    function insert(reading: Reading): void {
        if (view && mention.value) {
            onInsert(view, mention.value, reading);
        }

        close();
    }

    /** Runs before ProseMirror's own key handling while suggestions are open. */
    function onKeydown(event: KeyboardEvent): void {
        if (!mention.value) {
            return;
        }

        const reading = results.value[highlighted.value];

        switch (event.key) {
            case 'ArrowDown':
                highlighted.value = Math.min(
                    highlighted.value + 1,
                    results.value.length - 1,
                );
                break;
            case 'ArrowUp':
                highlighted.value = Math.max(highlighted.value - 1, 0);
                break;
            case 'Enter':
            case 'Tab':
                if (!reading) {
                    return;
                }

                insert(reading);
                break;
            case 'Escape':
                dismissedFrom = mention.value.from;
                close();
                break;
            default:
                return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    }

    function onSelectionChange(): void {
        requestAnimationFrame(update);
    }

    function attach(editorView: EditorView): void {
        view = editorView;
        view.dom.addEventListener('keydown', onKeydown, true);
        view.dom.addEventListener('input', onSelectionChange);
        view.dom.addEventListener('focusout', close);
        document.addEventListener('selectionchange', onSelectionChange);
        window.addEventListener('scroll', close, true);
    }

    function detach(): void {
        view?.dom.removeEventListener('keydown', onKeydown, true);
        view?.dom.removeEventListener('input', onSelectionChange);
        view?.dom.removeEventListener('focusout', close);
        document.removeEventListener('selectionchange', onSelectionChange);
        window.removeEventListener('scroll', close, true);
        view = null;
    }

    onBeforeUnmount(detach);

    return { mention, highlighted, results, loading, insert, attach };
}
