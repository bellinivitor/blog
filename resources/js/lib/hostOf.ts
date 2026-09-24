/** "martinfowler.com" from a full link, or null while it is not a valid URL. */
export function hostOf(url: string): string | null {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return null;
    }
}
