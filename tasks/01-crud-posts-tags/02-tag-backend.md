# Tag: backend

> Status: done · Ordem: 02 · Depende de: 01

## Objetivo
Implementar o domínio Tag (persistência, regras e rotas) para criar, listar, editar, excluir e restaurar tags.

## Contexto
- Arquivos/áreas envolvidas: migration `tags`; `app/Models/Tag/Tag.php`; `database/factories/TagFactory`; `Domain/Tag/{DataTransferObjects,Actions,QueryBuilders,Policies,Resources}`; `app/Http/Requests/Tag/*`; `app/Http/Controllers/TagController.php`; `routes/web.php`; `tests/{Unit,Feature}/Tag`.
- Como funciona hoje: não existe.
- Restrições: tabela `tags` com `id`, `name` (string), `slug` (string, unique), timestamps, `softDeletes`. Slug gerado do nome quando vazio, único considerando a lixeira. Controller thin retornando `Inertia::render` com Resources como props (páginas criadas na task 03 — os testes Feature validam o componente/props com `assertInertia`, sem exigir o arquivo Vue, ou criar stub mínimo se necessário). Rotas resource `tags.*` + `tags.restore` dentro de `auth` + `verified`. Escritas em transação nas Actions.

## Critérios de aceite
- [x] `tags.index` lista tags com busca por nome (QueryBuilder `search`) e filtro de lixeira; paginado.
- [x] `tags.store` / `tags.update` validam `name` obrigatório (máx. 255) e `slug` único; slug gerado quando vazio.
- [x] `tags.destroy` faz soft delete; `tags.restore` restaura.
- [x] Visitante não autenticado é redirecionado para login.
- [x] Testes Unit (Actions, DTO) e Feature (cada rota, validação, auth).

## Fora de escopo
- Telas Vue (task 03).
- Relação com posts (task 04).

## Definição de pronto
- [x] Testes escritos e passando (suíte completa sem quebrar)
- [x] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [x] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
