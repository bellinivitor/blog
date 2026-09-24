import type { Post } from '@/types';

/** Published with a date still in the future: hidden from the blog until then. */
export function isScheduled(
    post: Pick<Post, 'status' | 'published_at'>,
): boolean {
    return (
        post.status === 'published' &&
        post.published_at !== null &&
        new Date(post.published_at).getTime() > Date.now()
    );
}

export type PostState = 'draft' | 'scheduled' | 'published';

/** Where the post stands for readers: not out, waiting for its date, or live. */
export function postState(
    post: Pick<Post, 'status' | 'published_at'>,
): PostState {
    if (post.status === 'draft') {
        return 'draft';
    }

    return isScheduled(post) ? 'scheduled' : 'published';
}

export const postStateLabels: Record<PostState, string> = {
    draft: 'Draft',
    scheduled: 'Scheduled',
    published: 'Published',
};
