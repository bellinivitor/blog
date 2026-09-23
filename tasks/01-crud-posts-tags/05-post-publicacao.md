# Post: publicação

> Status: done · Ordem: 05 · Depende de: 04

## Objetivo

Permitir publicar e despublicar posts usando o State pattern da arquitetura.

## Contexto

- Arquivos/áreas envolvidas: `Domain/Post/Interfaces/PostStatusState.php`; `Domain/Post/States/{DraftState,PublishedState}.php`; `Domain/Post/Actions/ChangePostStatusAction.php`; rotas `posts.publish` / `posts.unpublish`; `PostController` (ou controller dedicado); `tests/{Unit,Feature}/Post`.
- Como funciona hoje: post nasce `draft` (task 04).
- Restrições: States puros (só retornam o enum; podem lançar exception em transição inválida). A Action persiste o status e ajusta `published_at` (preenche ao publicar se vazio; mantém ao despublicar) em transação. Policy: só o autor.

## Critérios de aceite

- [x] Publicar um rascunho muda o status para `published` e preenche `published_at`.
- [x] Despublicar volta para `draft`.
- [x] Publicar um já publicado (ou despublicar um rascunho) é rejeitado com erro adequado.
- [x] Não autor recebe 403.
- [x] Testes Unit (States, Action) e Feature (rotas).

## Fora de escopo

- Agendamento de publicação.
- Telas (task 06).

## Definição de pronto

- [x] Testes escritos e passando (suíte completa sem quebrar)
- [x] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [x] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [x] Segue os padrões do projeto (contrato do CLAUDE.md)
- [x] Sem segredos, sem comando destrutivo, sem mudança de dependência
