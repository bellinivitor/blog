import { $remark } from '@milkdown/kit/utils';

type MarkdownNode = {
    type: string;
    alt?: string | null;
    title?: string | null;
    children?: MarkdownNode[];
};

const IMAGE_TYPES = ['image', 'image-block'];

function fillImageText(node: MarkdownNode): void {
    if (IMAGE_TYPES.includes(node.type)) {
        node.alt ??= '';
        node.title ??= '';
    }

    node.children?.forEach(fillImageText);
}

/**
 * Markdown images without a title (`![alt](url)`, the usual form) parse with
 * `title: null`, which Milkdown's image schemas reject as a non-string
 * attribute: the image is dropped from the document, so it never shows in the
 * editor and vanishes from the Markdown on the next save. Empty strings keep
 * the images.
 */
export const remarkImageText = $remark(
    'remarkImageText',
    () => () => fillImageText,
);
