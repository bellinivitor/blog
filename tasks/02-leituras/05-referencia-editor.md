# Referência no post: editor

> Status: todo · Ordem: 05 · Depende de: 01, 04

## Objetivo

Botão "Inserir leitura" no editor Crepe para citar uma leitura no texto.

## Contexto

- Arquivos/áreas envolvidas: `resources/js/components/posts/MarkdownEditor.vue`; componente de diálogo de busca; endpoint JSON de busca em `ReadingController` (ou controller dedicado) sob `/admin`.
- Restrições: verificar a API de customização da toolbar/slash menu da versão instalada do `@milkdown/crepe` antes de implementar. Sem nova dependência.

## Critérios de aceite

- [ ] Botão/comando abre um diálogo que busca leituras por título.
- [ ] Com texto selecionado, vira link `leitura:ID`; sem seleção, insere o título da leitura como link.
- [ ] O Markdown salvo contém `[texto](leitura:ID)`.
- [ ] Endpoint de busca exige login e retorna no máximo alguns resultados.

## Fora de escopo

- Lista "Leituras citadas", "citada em".

## Definição de pronto

- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Testes de mutação nos arquivos tocados (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
