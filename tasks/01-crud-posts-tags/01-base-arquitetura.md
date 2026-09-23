# Base da arquitetura

> Status: done · Ordem: 01 · Depende de: —

## Objetivo

Criar as peças compartilhadas exigidas pela arquitetura para que os domínios Tag e Post possam ser construídos sobre elas.

## Contexto

- Arquivos/áreas envolvidas: `app/Models/DefaultModel.php`, `Domain/Shared/Interfaces/DataTransferObjectInterface.php`, `Domain/Shared/Exceptions/DomainHttpException.php`, `Domain/Shared/Exceptions/DomainException.php`.
- Como funciona hoje: só existe `app/Models/User.php`; `Domain\` já está mapeado no PSR-4 para `Domain/`.
- Restrições: seguir os templates do `docs/architecture/AGENTS.md` (seções DefaultModel, DTOs e Exceptions).

## Critérios de aceite

- [x] `DefaultModel` abstrato, estende `Model`, usa `HasFactory`, com PHPDoc de métodos de query.
- [x] `DataTransferObjectInterface` com `toArray`, `fromRequest`, `fromArray`.
- [x] `DomainHttpException` (estende `HttpException`) e `DomainException` (estende `RuntimeException`), ambas abstratas.
- [x] Autoload resolve as classes (`composer dump-autoload` sem erro; Larastan sem erro novo).

## Fora de escopo

- Migrar `User` para estender `DefaultModel`.
- Qualquer domínio concreto.

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [x] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [x] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
