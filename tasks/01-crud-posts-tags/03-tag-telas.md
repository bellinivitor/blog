# Tag: telas

> Status: todo · Ordem: 03 · Depende de: 02

## Objetivo
Criar as telas Inertia/Vue para gerenciar tags na área logada.

## Contexto
- Arquivos/áreas envolvidas: `resources/js/pages/tags/{Index,Create,Edit}.vue`; `resources/js/components/AppSidebar.vue` (item "Tags"); rotas via Wayfinder (`@/routes` / `@/actions`).
- Como funciona hoje: backend da task 02 pronto.
- Restrições: reusar componentes de `resources/js/components/ui` e o `AppLayout`; seguir o padrão das páginas `settings/*`; ativar as skills `inertia-vue-development`, `wayfinder-development` e `tailwindcss-development`.

## Critérios de aceite
- [ ] Listagem com busca, paginação, link para editar, excluir (com confirmação) e restaurar itens da lixeira.
- [ ] Formulários de criar/editar com erros de validação exibidos.
- [ ] Item "Tags" na sidebar.
- [ ] `npm run types:check` e build sem erro.

## Fora de escopo
- Mudanças de backend além de ajustes mínimos de props.

## Definição de pronto
- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [ ] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
