import type { Tag } from './tag';

export type PublishedPost = {
    title: string;
    slug: string;
    excerpt: string | null;
    published_at: string;
    revised_at: string | null;
    reading_minutes: number;
    tags: Tag[];
};

export type BlogProfile = {
    author: string;
    headline: string;
    bio: string;
    links: { label: string; url: string }[];
};
