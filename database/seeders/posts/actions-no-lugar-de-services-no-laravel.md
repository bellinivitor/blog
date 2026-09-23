Toda vez que um controller passa de cem linhas, alguém sugere criar um *service*. Eu sugeria também, até perceber que o problema não era o tamanho do arquivo, e sim a mistura de responsabilidades.

## O que muda com uma Action

Uma Action faz **uma coisa** e recebe um DTO. O controller só autoriza, monta o DTO e chama:

```php
public function store(StorePostRequest $request, StorePostAction $action): RedirectResponse
{
    Gate::authorize('create', Post::class);

    $post = $action(PostDTO::fromRequest($request), $request->user());

    return to_route('posts.edit', $post);
}
```

Isso deixa a regra de negócio testável sem HTTP, e o `__invoke` único impede que a classe vire um depósito.

> Se você precisa de "e" para descrever o que a classe faz, ela faz coisas demais.

### O custo

- Mais arquivos, cada um pequeno.
- Nomes precisam ser bons, porque são a documentação.
- Transações ficam explícitas em cada escrita.

## Onde isso não funciona

Num CRUD sem regra nenhuma, uma Action por operação é cerimônia. Eu mantenho mesmo assim pela consistência, mas entendo quem não mantém.

## Conclusão

O ganho aparece no terceiro mês, quando você volta ao código e entende tudo em dois minutos.
