export type Tag = {
    id: number;
    name: string;
    slug: string;
    deleted_at: string | null;
};

export type TagFilters = {
    search: string | null;
    trashed: boolean;
};
