# Referência no post: editor

> Status: done · Ordem: 05 · Depende de: 01, 04

## Objetivo

Botão "Inserir leitura" no editor Crepe para citar uma leitura no texto.

## Contexto

- Arquivos/áreas envolvidas: `resources/js/components/posts/MarkdownEditor.vue`; componente de diálogo de busca; endpoint JSON de busca em `ReadingController` (ou controller dedicado) sob `/admin`.
- Restrições: verificar a API de customização da toolbar/slash menu da versão instalada do `@milkdown/crepe` antes de implementar. Sem nova dependência.

## Critérios de aceite

- [x] Botão/comando abre um diálogo que busca leituras por título.
- [x] Com texto selecionado, vira link `leitura:ID`; sem seleção, insere o título da leitura como link.
- [ ] O Markdown salvo contém `[texto](leitura:ID)` — não verificado no navegador (o painel exige login); conferir manualmente.
- [x] Endpoint de busca exige login e retorna no máximo alguns resultados.

## Notas

- A view do editor vem de `ctx.get('editorView')` no `onRun` da top bar, com imports só de tipo de `@milkdown/kit` (dependência transitiva da Crepe), para não adicionar dependência.

## Fora de escopo

- Lista "Leituras citadas", "citada em".

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [ ] ~~Testes de mutação~~ — Infection não instalado (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
