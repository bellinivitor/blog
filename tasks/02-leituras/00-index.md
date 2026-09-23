# 02 — Leituras

> Status: doing

## Objetivo

Cadastrar leituras recomendadas (título e link), mostrá-las numa página pública acessível pelo rodapé e poder citá-las no texto dos posts.

## Critérios de aceite

- [x] Admin `/admin/readings`: criar, listar (busca), editar, excluir (soft delete) e restaurar leituras.
- [x] Seeder com leituras de exemplo para testar localmente.
- [ ] Página pública `/leituras`: lista única, mais recentes primeiro; cada item é um link (nova aba). Link "Leituras" no rodapé; página no sitemap.
- [ ] Editor de post: botão "Inserir leitura" busca uma leitura e transforma o trecho selecionado em link (sem seleção, insere o título).
- [ ] Post publicado: referência vira link para a URL atual da leitura; leitura excluída vira texto simples.
- [ ] Alterar o link de uma leitura atualiza os posts que a citam (cache do HTML invalidado).

## Decisões

- **Campos:** só título e link (URL http/https). Tudo é link, sem tipo.
- **No texto:** só o link, sem lista "Leituras citadas" no fim nem "citada em".
- **Página:** lista única, mais recentes primeiro.
- **Afiliado:** nunca; links diretos.
- **Suposições:** referência salva no Markdown como `[texto](leitura:ID)` e resolvida na renderização; soft delete + restore como em tags; leituras globais (qualquer usuário logado gerencia), como tags.

## Como testar

- `php artisan test --compact tests/Feature/Reading tests/Feature/Blog tests/Feature/Post`
- `php artisan db:seed --class=ReadingSeeder` e navegar em `/leituras`, `/admin/readings` e no editor de post.

## Fora de escopo

Autor, comentário, tipo, tags de leitura, links de afiliado, seção "Leituras citadas", "citada em".

## Tasks

- [x] [01 — Leitura: backend e seeder](01-leitura-backend.md) · done
- [ ] [02 — Leitura: telas do admin](02-leitura-telas.md) · todo
- [ ] [03 — Página pública de leituras](03-pagina-publica.md) · todo
- [ ] [04 — Referência no post: renderização](04-referencia-renderizacao.md) · todo
- [ ] [05 — Referência no post: editor](05-referencia-editor.md) · todo
