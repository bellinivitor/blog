O Eloquent já é uma camada de abstração sobre o banco. Colocar um Repository por cima costuma virar um repasse de métodos que esconde o que o ORM tem de melhor.

## O problema do Repository sobre ORM

Com o tempo aparecem `findByEmail`, `findActiveByEmail`, `findActiveByEmailWithRoles`... Cada combinação vira um método, e o eager loading some.

## Um QueryBuilder com nome

A alternativa é estender o `Builder` e dar nome às consultas:

```php
class PostQueryBuilder extends Builder
{
    public function published(): static
    {
        return $this->where('status', PostStatus::Published)
            ->where('published_at', '<=', now());
    }
}
```

No model, basta apontar para ele:

```php
public function newEloquentBuilder($query): PostQueryBuilder
{
    return new PostQueryBuilder($query);
}
```

E o uso continua sendo Eloquent puro: `Post::query()->published()->with('tags')->paginate()`.

### O que você ganha

1. Consultas encadeáveis e reutilizáveis.
2. Relacionamentos, paginação e `chunk` continuam funcionando.
3. Um lugar óbvio para procurar filtros.

## Conclusão

Dê nome às consultas, não esconda o ORM.
