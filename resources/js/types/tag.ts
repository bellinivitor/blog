export type Tag = {
    id: number;
    name: string;
    slug: string;
    /** How many posts use the tag; only on the tag list. */
    posts_count?: number;
    deleted_at: string | null;
};

export type TagFilters = {
    search: string | null;
    trashed: boolean;
};
