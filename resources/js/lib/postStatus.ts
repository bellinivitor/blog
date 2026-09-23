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
