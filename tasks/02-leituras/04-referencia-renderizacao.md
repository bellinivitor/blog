# Referência no post: renderização

> Status: done · Ordem: 04 · Depende de: 01

## Objetivo

Resolver links `leitura:ID` do Markdown para a URL atual da leitura ao renderizar o post.

## Contexto

- Arquivos/áreas envolvidas: `Domain/Post/Actions/RenderPostContentAction.php` (CommonMark); possivelmente um listener/extension em `Domain/Post`; testes em `tests/Feature/Blog`.
- Como funciona hoje: HTML cacheado por `posts.{id}.html.v{N}.{updated_at}`.
- Restrições: um só query para as leituras citadas; bump de `RENDERER_VERSION`; a chave de cache passa a incluir a versão das leituras (ex.: maior `updated_at`, com lixeira).

## Critérios de aceite

- [x] `[texto](leitura:ID)` vira `<a href="URL da leitura">texto</a>` abrindo em nova aba (`rel="noopener"`).
- [x] Leitura inexistente ou excluída: o texto aparece sem link.
- [x] Alterar a URL de uma leitura muda o HTML do post na próxima renderização.

## Fora de escopo

- Editor (task 05).

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [ ] ~~Testes de mutação~~ — Infection não instalado (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
