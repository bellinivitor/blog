# Post: telas

> Status: todo · Ordem: 06 · Depende de: 03, 05

## Objetivo
Criar as telas Inertia/Vue para gerenciar posts na área logada.

## Contexto
- Arquivos/áreas envolvidas: `resources/js/pages/posts/{Index,Create,Edit}.vue` (e um componente de formulário compartilhado, se fizer sentido); `resources/js/components/AppSidebar.vue` (item "Posts"); rotas via Wayfinder.
- Como funciona hoje: backend das tasks 04 e 05 pronto; telas de tag (task 03) servem de referência.
- Restrições: reusar `components/ui`, `AppLayout` e o padrão das telas de tag; ativar as skills `inertia-vue-development`, `wayfinder-development` e `tailwindcss-development`.

## Critérios de aceite
- [ ] Listagem com busca por título, filtros por status e tag, lixeira, badges de status, paginação.
- [ ] Formulário com título, slug (opcional), excerpt, textarea de Markdown e seleção de tags; erros de validação exibidos.
- [ ] Botões de publicar/despublicar, excluir (com confirmação) e restaurar.
- [ ] Item "Posts" na sidebar.
- [ ] `npm run types:check` e build sem erro.

## Fora de escopo
- Preview/renderização de Markdown; upload de imagem.

## Definição de pronto
- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [ ] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
