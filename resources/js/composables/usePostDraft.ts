import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

export type PostDraftFields = {
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    tag_ids: string[];
    new_tags: string[];
};

type StoredDraft = {
    savedAt: string;
    fields: PostDraftFields;
};

const SNAPSHOT_EVERY_MS = 3000;

function readFields(form: HTMLFormElement): PostDraftFields {
    const data = new FormData(form);
    const text = (name: string): string => {
        const value = data.get(name);

        return typeof value === 'string' ? value : '';
    };
    const texts = (name: string): string[] =>
        data
            .getAll(name)
            .filter((value): value is string => typeof value === 'string');

    return {
        title: text('title'),
        slug: text('slug'),
        excerpt: text('excerpt'),
        content: text('content'),
        tag_ids: texts('tag_ids[]'),
        new_tags: texts('new_tags[]'),
    };
}

function read(key: string): StoredDraft | null {
    try {
        const raw = localStorage.getItem(key);

        return raw ? (JSON.parse(raw) as StoredDraft) : null;
    } catch {
        return null;
    }
}

function write(key: string, draft: StoredDraft | null): void {
    try {
        if (draft) {
            localStorage.setItem(key, JSON.stringify(draft));
        } else {
            localStorage.removeItem(key);
        }
    } catch {
        // Storage may be full or blocked (private mode); the backup is best effort.
    }
}

/**
 * Local backup of an unsaved post form, kept in this browser only. Every few
 * seconds the form is copied to localStorage when it differs from what the
 * server has; a newer copy found on load is offered for restore.
 *
 * @param key       storage key, one per post ("new" for the create form)
 * @param savedAt   when the server copy was last saved (null for a new post)
 * @param container element wrapping the <form>
 */
export function usePostDraft(
    key: string,
    savedAt: string | null,
    container: Readonly<Ref<HTMLElement | null>>,
) {
    const storageKey = `post-draft:${key}`;
    const pendingDraft = ref<StoredDraft | null>(null);

    let initial = '';
    let lastSaved = '';
    /** Set after a successful save, until the form is rebased on the new server copy. */
    let paused = false;
    let timer: ReturnType<typeof setInterval> | undefined;

    function form(): HTMLFormElement | null {
        return container.value?.querySelector('form') ?? null;
    }

    function snapshot(): void {
        const element = form();

        if (!element || pendingDraft.value || paused) {
            return;
        }

        const current = JSON.stringify(readFields(element));

        if (current === lastSaved) {
            return;
        }

        lastSaved = current;
        write(
            storageKey,
            current === initial
                ? null
                : {
                      savedAt: new Date().toISOString(),
                      fields: JSON.parse(current),
                  },
        );
    }

    /**
     * Take the form as it is now as the server baseline and resume copying.
     * Call after a successful save, once the form shows the saved values.
     * Not after a restore: restored text is still unsaved.
     */
    function rebase(): void {
        const element = form();
        initial = element ? JSON.stringify(readFields(element)) : '';
        lastSaved = initial;
        paused = false;
    }

    /** Drop the local copy (after a save, or when the author discards it). */
    function clear(): void {
        write(storageKey, null);
        pendingDraft.value = null;
        paused = true;
    }

    /** Discard the offered copy and keep editing the server version. */
    function discard(): void {
        clear();
        paused = false;
    }

    onMounted(() => {
        rebase();

        const stored = read(storageKey);
        const isNewer =
            !savedAt ||
            (stored && new Date(stored.savedAt) > new Date(savedAt));

        if (stored && isNewer && JSON.stringify(stored.fields) !== initial) {
            pendingDraft.value = stored;
        } else if (stored) {
            write(storageKey, null);
        }

        timer = setInterval(snapshot, SNAPSHOT_EVERY_MS);
    });

    onBeforeUnmount(() => {
        snapshot();
        clearInterval(timer);
    });

    return {
        pendingDraft,
        /** Hand the stored fields to the caller and stop offering them. */
        takePendingDraft(): PostDraftFields | null {
            const fields = pendingDraft.value?.fields ?? null;
            pendingDraft.value = null;

            return fields;
        },
        rebase,
        clear,
        discard,
    };
}
