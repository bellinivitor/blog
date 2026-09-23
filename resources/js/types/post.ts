import type { Tag } from './tag';

export type PostStatus = 'draft' | 'published';

export type Post = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    status: PostStatus;
    published_at: string | null;
    views_count: number;
    /** Public preview link, or null while sharing is disabled. */
    preview_url: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    tags: Tag[];
};

export type PostFilters = {
    search: string | null;
    status: PostStatus | null;
    tag_id: number | null;
    trashed: boolean;
};

export type MostReadPost = {
    id: number;
    title: string;
    slug: string;
    views_count: number;
};

export type DailyViews = {
    /** Calendar day in the blog timezone, "YYYY-MM-DD". */
    date: string;
    views: number;
};
