# 01 — CRUD de Posts e Tags (admin)

> Status: doing

## Objetivo
Gerenciar posts e tags do blog pessoal na área logada, com telas Inertia/Vue e backend seguindo a arquitetura domain-driven (`docs/architecture/AGENTS.md`).

## Critérios de aceite
- [ ] Tags: criar, listar, editar, excluir (soft delete) e restaurar.
- [ ] Posts: criar, listar (busca por título, status e tag), editar, excluir (soft delete) e restaurar.
- [ ] Post tem título, slug, resumo (opcional), conteúdo em Markdown, status `draft`/`published`, `published_at` e N tags.
- [ ] Publicar/despublicar post (publicar preenche `published_at`).
- [ ] Só usuário autenticado acessa; post pertence ao autor (policy).
- [ ] Testes Unit (Actions/DTOs) e Feature (rotas) passando.

## Decisões
- **Escopo:** só o admin; páginas públicas ficam para outra feature.
- **Conteúdo:** Markdown salvo cru (renderização/preview fora desta feature).
- **Status:** enum `PostStatus` (`draft`/`published`) + State pattern + `ChangePostStatusAction`.
- **Exclusão:** soft delete para posts e tags, com restauração.
- **Suposições:** post seleciona tags existentes (sem criação inline); slug gerado do título/nome quando vazio, editável e único considerando a lixeira; tags globais para usuário logado; policy de post exige autoria; Resources alimentam as props do Inertia; pastas do `Domain/` criadas à mão (não há `make:domain`).

## Como testar
- `php artisan test --compact tests/Feature/Tag tests/Unit/Tag tests/Feature/Post tests/Unit/Post`
- Navegar nas telas com `composer run dev`.

## Fora de escopo
Páginas públicas, preview de Markdown, upload de imagem, SEO/meta, agendamento de publicação, comentários.

## Tasks
- [ ] [01 — Base da arquitetura](01-base-arquitetura.md) · todo
- [ ] [02 — Tag: backend](02-tag-backend.md) · todo
- [ ] [03 — Tag: telas](03-tag-telas.md) · todo
- [ ] [04 — Post: backend CRUD](04-post-backend.md) · todo
- [ ] [05 — Post: publicação](05-post-publicacao.md) · todo
- [ ] [06 — Post: telas](06-post-telas.md) · todo
