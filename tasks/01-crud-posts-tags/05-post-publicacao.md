# Post: publicação

> Status: todo · Ordem: 05 · Depende de: 04

## Objetivo
Permitir publicar e despublicar posts usando o State pattern da arquitetura.

## Contexto
- Arquivos/áreas envolvidas: `Domain/Post/Interfaces/PostStatusState.php`; `Domain/Post/States/{DraftState,PublishedState}.php`; `Domain/Post/Actions/ChangePostStatusAction.php`; rotas `posts.publish` / `posts.unpublish`; `PostController` (ou controller dedicado); `tests/{Unit,Feature}/Post`.
- Como funciona hoje: post nasce `draft` (task 04).
- Restrições: States puros (só retornam o enum; podem lançar exception em transição inválida). A Action persiste o status e ajusta `published_at` (preenche ao publicar se vazio; mantém ao despublicar) em transação. Policy: só o autor.

## Critérios de aceite
- [ ] Publicar um rascunho muda o status para `published` e preenche `published_at`.
- [ ] Despublicar volta para `draft`.
- [ ] Publicar um já publicado (ou despublicar um rascunho) é rejeitado com erro adequado.
- [ ] Não autor recebe 403.
- [ ] Testes Unit (States, Action) e Feature (rotas).

## Fora de escopo
- Agendamento de publicação.
- Telas (task 06).

## Definição de pronto
- [ ] Testes escritos e passando (suíte completa sem quebrar)
- [ ] Pint rodado (`vendor/bin/pint --dirty --format agent`)
- [ ] Checklist de revisão do `docs/architecture/AGENTS.md` atendido
- [ ] Segue os padrões do projeto (contrato do CLAUDE.md)
- [ ] Sem segredos, sem comando destrutivo, sem mudança de dependência
