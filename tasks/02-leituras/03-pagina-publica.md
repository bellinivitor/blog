# Página pública de leituras

> Status: todo · Ordem: 03 · Depende de: 01

## Objetivo

Página `/leituras` no visual do blog, acessível pelo rodapé.

## Contexto

- Arquivos/áreas envolvidas: `app/Http/Controllers/Blog/BlogReadingController.php`; `resources/js/pages/blog/Readings.vue`; `resources/js/layouts/BlogLayout.vue`; `routes/web.php` (antes do catch-all `{slug}`); `resources/views/blog/sitemap.blade.php` / `SeoController`.
- Restrições: seguir o visual do blog (`resources/css/blog.css`, BlogLayout). Meta via `PageMetaDTO`. O slug `leituras` fica reservado automaticamente para posts.

## Critérios de aceite

- [ ] Lista leituras não excluídas, mais recentes primeiro; cada uma é link com `target="_blank" rel="noopener"`.
- [ ] Estado vazio quando não há leituras.
- [ ] Link "Leituras" no rodapé do blog.
- [ ] `/leituras` no sitemap.

## Fora de escopo

- Agrupamento, tags, "citada em".

## Definição de pronto

- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Testes de mutação nos arquivos tocados (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
