# Leitura: backend e seeder

> Status: done · Ordem: 01 · Depende de: —

## Objetivo

Implementar o domínio Reading (persistência, regras, rotas do admin) e um seeder com leituras de exemplo.

## Contexto

- Arquivos/áreas envolvidas: migration `readings`; `app/Models/Reading/Reading.php`; `database/factories/Reading/ReadingFactory.php`; `Domain/Reading/{DataTransferObjects,Actions,QueryBuilders,Policies,Resources}`; `app/Http/Requests/Reading/*`; `app/Http/Controllers/ReadingController.php`; `routes/web.php`; `database/seeders/ReadingSeeder.php`; `tests/Feature/Reading`.
- Como funciona hoje: não existe. Espelhar o domínio Tag.
- Restrições: tabela `readings` com `id`, `title` (string), `url` (string, 2048), timestamps, `softDeletes`. URL obrigatória, só http/https. Rotas resource `readings.*` (sem show) + `readings.restore` em `admin` com `auth` + `verified`. Escritas em transação.

## Critérios de aceite

- [x] `readings.index` lista com busca por título e filtro de lixeira; paginado.
- [x] `readings.store` / `readings.update` validam `title` (obrigatório, máx. 255) e `url` (obrigatória, http/https, máx. 2048).
- [x] `readings.destroy` faz soft delete; `readings.restore` restaura.
- [x] Visitante não autenticado é redirecionado para login.
- [x] `ReadingSeeder` cria leituras de exemplo reais, pode rodar de novo sem duplicar (casando por URL) e é chamado pelo `DatabaseSeeder`.

## Notas

- Páginas `readings/*.vue` criadas como stub mínimo para os testes Inertia (manifest do Vite); substituídas na task 02.

## Fora de escopo

- Telas Vue (task 02), página pública (task 03), referência em posts (tasks 04–05).

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [ ] ~~Testes de mutação~~ — Infection não está instalado no projeto (adicionar exige confirmar dependência) (ex.: Infection sobre o diff): mutantes escapados relevantes mortos e MSI ≥ limite do projeto (ignore equivalentes; foque no domínio, não em boilerplate)
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
