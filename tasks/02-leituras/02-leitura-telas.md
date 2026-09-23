# Leitura: telas do admin

> Status: done · Ordem: 02 · Depende de: 01

## Objetivo

Telas Inertia/Vue para gerenciar leituras, no mesmo padrão das telas de tags.

## Contexto

- Arquivos/áreas envolvidas: `resources/js/pages/readings/{Index,Create,Edit}.vue`; componentes em `resources/js/components/readings` se preciso; `resources/js/lib/navigation.ts`; tipos em `resources/js/types`.
- Como funciona hoje: só o backend (task 01).
- Restrições: reaproveitar componentes de tags (paginação, diálogo de exclusão, busca). Wayfinder para rotas.

## Critérios de aceite

- [x] Index com busca, lixeira, paginação, excluir e restaurar; link para abrir a URL.
- [x] Create/Edit com título e link, mostrando erros de validação.
- [x] Item "Readings" no menu lateral.

## Fora de escopo

- Página pública e editor de post.

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [ ] ~~Testes de mutação~~ — Infection não instalado; task só de telas (sem lógica de domínio) (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
