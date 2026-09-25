import type { Mermaid } from 'mermaid';

/*
 * Mermaid diagrams (```mermaid blocks), drawn in the browser. The library is
 * heavy, so it only loads when a page has a diagram.
 */

/** The public blog's paper, ink and pen (resources/css/blog.css). */
const PALETTES = {
    light: {
        background: '#f7f2e6',
        ink: '#262219',
        graphite: '#6b6456',
        rule: '#e2d9c4',
        pen: '#af3a03',
        panel: '#eee6d3',
    },
    dark: {
        background: '#141922',
        ink: '#dae0ea',
        graphite: '#8e98aa',
        rule: '#283040',
        pen: '#8ea6ff',
        panel: '#1b2230',
    },
};

let library: Promise<Mermaid> | null = null;
let configuredFor: 'light' | 'dark' | null = null;
let renders = 0;

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

async function load(): Promise<Mermaid> {
    library ??= import('mermaid').then((module) => module.default);

    const mermaid = await library;
    const scheme = isDark() ? 'dark' : 'light';

    if (configuredFor !== scheme) {
        const palette = PALETTES[scheme];

        mermaid.initialize({
            startOnLoad: false,
            securityLevel: 'strict',
            theme: 'base',
            darkMode: scheme === 'dark',
            fontFamily:
                "'Monaspace Neon', ui-monospace, SFMono-Regular, Menlo, monospace",
            themeVariables: {
                background: palette.background,
                primaryColor: palette.panel,
                primaryTextColor: palette.ink,
                primaryBorderColor: palette.graphite,
                secondaryColor: palette.background,
                tertiaryColor: palette.background,
                lineColor: palette.graphite,
                textColor: palette.ink,
                mainBkg: palette.panel,
                nodeBorder: palette.graphite,
                clusterBkg: palette.background,
                clusterBorder: palette.rule,
                edgeLabelBackground: palette.background,
                noteBkgColor: palette.panel,
                noteBorderColor: palette.pen,
                noteTextColor: palette.ink,
                actorBkg: palette.panel,
                actorBorder: palette.graphite,
                actorTextColor: palette.ink,
                signalColor: palette.ink,
                signalTextColor: palette.ink,
            },
        });
        configuredFor = scheme;
    }

    return mermaid;
}

/** Draw a diagram's source to SVG markup; throws when the source is invalid. */
export async function renderMermaid(source: string): Promise<string> {
    const mermaid = await load();
    const id = `mermaid-${++renders}`;

    try {
        const { svg } = await mermaid.render(id, source);

        return svg;
    } finally {
        // A failed render leaves its scratch element (#d<id>) in the body.
        document.getElementById(`d${id}`)?.remove();
    }
}

/**
 * Replace each <pre class="mermaid"> in the root with its diagram, keeping
 * the source to redraw when the color scheme changes. An invalid diagram
 * stays as its source text.
 */
export async function renderMermaidBlocks(root: HTMLElement): Promise<void> {
    const blocks = root.querySelectorAll<HTMLElement>('pre.mermaid');

    for (const block of blocks) {
        block.dataset.source ??= block.textContent ?? '';

        try {
            block.innerHTML = await renderMermaid(block.dataset.source);
            block.classList.add('mermaid-drawn');
        } catch {
            block.textContent = block.dataset.source;
            block.classList.remove('mermaid-drawn');
        }
    }
}
