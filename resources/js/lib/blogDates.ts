const shortMonths = [
    'jan',
    'fev',
    'mar',
    'abr',
    'mai',
    'jun',
    'jul',
    'ago',
    'set',
    'out',
    'nov',
    'dez',
];

/** "23 set" */
export function formatShortDate(iso: string): string {
    const date = new Date(iso);

    return `${String(date.getDate()).padStart(2, '0')} ${shortMonths[date.getMonth()]}`;
}

/** "23 de setembro de 2026" */
export function formatLongDate(iso: string): string {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long' }).format(
        new Date(iso),
    );
}

export function yearOf(iso: string): number {
    return new Date(iso).getFullYear();
}
