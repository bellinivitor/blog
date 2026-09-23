Separar um monólito Laravel por domínio parecia só mover pastas. Não era.

## Erro 1: domínios demais

Criei um domínio por tabela. Em duas semanas, metade das Actions importava de três domínios diferentes. Domínio é uma área de negócio, não uma tabela.

## Erro 2: HTTP dentro do domínio

Actions recebendo `Request` pareciam práticas até eu precisar chamá-las de um job. A regra que ficou: o domínio recebe DTO e nunca sabe que existe HTTP.

```php
// Antes
public function __invoke(StorePostRequest $request): Post

// Depois
public function __invoke(PostDTO $postDTO, User $author): Post
```

## Erro 3: interface para tudo

Cada Action tinha uma interface com uma única implementação. Isso não desacopla nada, só dobra o número de arquivos.

## O que ficou

- Controllers finos.
- Actions pequenas, com transação.
- QueryBuilders no lugar de repositórios.
- Resources em toda resposta.
