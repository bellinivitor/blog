# Tag: telas

> Status: done · Ordem: 03 · Depende de: 02

## Objetivo

Criar as telas Inertia/Vue para gerenciar tags na área logada.

## Contexto

- Arquivos/áreas envolvidas: `resources/js/pages/tags/{Index,Create,Edit}.vue`; `resources/js/components/AppSidebar.vue` (item "Tags"); rotas via Wayfinder (`@/routes` / `@/actions`).
- Como funciona hoje: backend da task 02 pronto.
- Restrições: reusar componentes de `resources/js/components/ui` e o `AppLayout`; seguir o padrão das páginas `settings/*`; ativar as skills `inertia-vue-development`, `wayfinder-development` e `tailwindcss-development`.

## Critérios de aceite

- [x] Listagem com busca, paginação, link para editar, excluir (com confirmação) e restaurar itens da lixeira.
- [x] Formulários de criar/editar com erros de validação exibidos.
- [x] Item "Tags" na sidebar.
- [x] `npm run types:check` e build sem erro.

## Fora de escopo

- Mudanças de backend além de ajustes mínimos de props.

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [x] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [x] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
