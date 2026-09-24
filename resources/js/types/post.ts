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
    likes_count: number;
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

/** How many posts the author has in each tab of the post list. */
export type PostCounts = {
    all: number;
    draft: number;
    published: number;
    trashed: number;
};

export type MostReadPost = {
    id: number;
    title: string;
    slug: string;
    views_count: number;
    likes_count: number;
};

/** A post as listed on the dashboard, without its content. */
export type PostSummary = {
    id: number;
    title: string;
    excerpt: string | null;
    published_at: string | null;
    updated_at: string | null;
};

export type DailyViews = {
    /** Calendar day in the blog timezone, "YYYY-MM-DD". */
    date: string;
    views: number;
};
