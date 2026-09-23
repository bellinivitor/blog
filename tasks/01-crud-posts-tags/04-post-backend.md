# Post: backend CRUD

> Status: todo · Ordem: 04 · Depende de: 02

## Objetivo
Implementar o domínio Post com CRUD, relação N:N com tags, soft delete e restauração.

## Contexto
- Arquivos/áreas envolvidas: migrations `posts` e `post_tag`; `app/Models/Post/Post.php`; `database/factories/PostFactory`; `Domain/Post/{Enums,DataTransferObjects,Actions,QueryBuilders,Policies,Resources}`; `app/Http/Requests/Post/*`; `app/Http/Controllers/PostController.php`; `routes/web.php`; `tests/{Unit,Feature}/Post`.
- Como funciona hoje: não existe; Tag pronto (task 02).
- Restrições: `posts`: `id`, `author_id` (FK users), `title`, `slug` (unique), `excerpt` (nullable text), `content` (longText, Markdown), `status` (string, default `draft`), `published_at` (nullable), timestamps, `softDeletes`. Pivot `post_tag` (`post_id`, `tag_id`, FKs com cascade). Enum `PostStatus` (`Draft`, `Published`) com cast. Post criado sempre como rascunho. Slug gerado do título quando vazio, único considerando a lixeira. Actions de store/update fazem `sync` das tags na mesma transação. Policy: só o autor edita/exclui/restaura.

## Critérios de aceite
- [ ] `posts.index` lista posts do usuário com busca por título, status e tag (QueryBuilder `search` recebendo DTO), filtro de lixeira, eager load de tags, paginado.
- [ ] `posts.store` / `posts.update` validam título, slug, excerpt, content e `tag_ids` (existentes e não excluídas) e sincronizam as tags.
- [ ] `posts.destroy` faz soft delete; `posts.restore` restaura.
- [ ] Usuário que não é autor recebe 403 em edit/update/destroy/restore.
- [ ] Testes Unit (Actions, DTO, QueryBuilder) e Feature (rotas, validação, auth, policy).

## Fora de escopo
- Publicar/despublicar (task 05).
- Telas Vue (task 06).
- Renderização do Markdown.

## Definição de pronto
- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [ ] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
