import {
    HighlightStyle,
    LanguageDescription,
    syntaxHighlighting,
} from '@codemirror/language';
import { languages } from '@codemirror/language-data';
import { tags } from '@lezer/highlight';

/*
 * Code blocks in the post editor. These packages come with @milkdown/crepe,
 * which uses them for its own code blocks.
 */

/**
 * The editor's languages, with PHP starting in PHP mode: the stock one
 * expects an opening "<?php" tag and leaves snippets without it uncolored.
 */
export const codeLanguages = languages.map((language) =>
    language.name === 'PHP'
        ? LanguageDescription.of({
              name: language.name,
              alias: language.alias,
              extensions: language.extensions,
              load: () =>
                  import('@codemirror/lang-php').then(({ php }) =>
                      php({ plain: true }),
                  ),
          })
        : language,
);

/**
 * Token colors from the GitHub themes the public post page uses (Phiki),
 * so code looks the same while writing. The CSS variables switch between
 * light and dark in MarkdownEditor.vue.
 */
export const codeHighlighting = syntaxHighlighting(
    HighlightStyle.define([
        {
            tag: [tags.keyword, tags.operatorKeyword, tags.modifier],
            color: 'var(--code-keyword)',
        },
        {
            tag: [
                tags.function(tags.variableName),
                tags.function(tags.propertyName),
                tags.className,
                tags.typeName,
                tags.namespace,
            ],
            color: 'var(--code-entity)',
        },
        {
            tag: [tags.string, tags.special(tags.string), tags.regexp],
            color: 'var(--code-string)',
        },
        {
            tag: [
                tags.number,
                tags.bool,
                tags.null,
                tags.atom,
                tags.constant(tags.variableName),
                tags.propertyName,
                tags.attributeName,
            ],
            color: 'var(--code-constant)',
        },
        {
            tag: [
                tags.variableName,
                tags.special(tags.variableName),
                tags.self,
            ],
            color: 'var(--code-variable)',
        },
        { tag: [tags.tagName], color: 'var(--code-tag)' },
        {
            tag: [tags.comment, tags.meta],
            color: 'var(--code-comment)',
            fontStyle: 'italic',
        },
        { tag: tags.invalid, color: 'var(--code-keyword)' },
    ]),
);
