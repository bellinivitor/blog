/** The admin is written in English, so its dates read as English sentences. */
const locale = 'en';

const relativeFormat = new Intl.RelativeTimeFormat(locale, {
    numeric: 'auto',
});

const units: [Intl.RelativeTimeFormatUnit, number][] = [
    ['year', 365 * 86_400],
    ['month', 30 * 86_400],
    ['week', 7 * 86_400],
    ['day', 86_400],
    ['hour', 3_600],
    ['minute', 60],
];

/** "Sep 12, 2026" */
export function formatDate(iso: string): string {
    return new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(
        new Date(iso),
    );
}

/** "Sep 12, 2026, 10:30 AM" */
export function formatDateTime(iso: string): string {
    return new Intl.DateTimeFormat(locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
}

/** "3 days ago", "in 2 hours", "now" */
export function formatRelative(iso: string, now: number = Date.now()): string {
    const seconds = (new Date(iso).getTime() - now) / 1000;

    for (const [unit, size] of units) {
        if (Math.abs(seconds) >= size) {
            return relativeFormat.format(Math.round(seconds / size), unit);
        }
    }

    return relativeFormat.format(0, 'second');
}
