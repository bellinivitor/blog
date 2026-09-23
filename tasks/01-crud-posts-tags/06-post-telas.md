# Post: telas

> Status: done · Ordem: 06 · Depende de: 03, 05

## Objetivo

Criar as telas Inertia/Vue para gerenciar posts na área logada.

## Contexto

- Arquivos/áreas envolvidas: `resources/js/pages/posts/{Index,Create,Edit}.vue` (e um componente de formulário compartilhado, se fizer sentido); `resources/js/components/AppSidebar.vue` (item "Posts"); rotas via Wayfinder.
- Como funciona hoje: backend das tasks 04 e 05 pronto; telas de tag (task 03) servem de referência.
- Restrições: reusar `components/ui`, `AppLayout` e o padrão das telas de tag; ativar as skills `inertia-vue-development`, `wayfinder-development` e `tailwindcss-development`.

## Critérios de aceite

- [x] Listagem com busca por título, filtros por status e tag, lixeira, badges de status, paginação.
- [x] Formulário com título, slug (opcional), excerpt, textarea de Markdown e seleção de tags; erros de validação exibidos.
- [x] Botões de publicar/despublicar, excluir (com confirmação) e restaurar.
- [x] Item "Posts" na sidebar.
- [x] `npm run types:check` e build sem erro.

## Fora de escopo

- Preview/renderização de Markdown; upload de imagem.

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [x] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [x] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
