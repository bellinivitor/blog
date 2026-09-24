export type Reading = {
    id: number;
    title: string;
    url: string;
    /** How many posts cite the reading; only on the reading list. */
    citations_count?: number;
    created_at: string | null;
    deleted_at: string | null;
};

export type ReadingFilters = {
    search: string | null;
    trashed: boolean;
};
