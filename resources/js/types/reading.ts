export type Reading = {
    id: number;
    title: string;
    url: string;
    created_at: string | null;
    deleted_at: string | null;
};

export type ReadingFilters = {
    search: string | null;
    trashed: boolean;
};
