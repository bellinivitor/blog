import { BookOpen, FileText, LayoutGrid, Tags } from '@lucide/vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import ReadingController from '@/actions/App/Http/Controllers/ReadingController';
import TagController from '@/actions/App/Http/Controllers/TagController';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Posts',
        href: PostController.index(),
        icon: FileText,
    },
    {
        title: 'Tags',
        href: TagController.index(),
        icon: Tags,
    },
    {
        title: 'Readings',
        href: ReadingController.index(),
        icon: BookOpen,
    },
];
