Uma escrita que toca duas tabelas sem transação funciona 99% das vezes. O 1% é o que aparece no suporte.

## O padrão que uso

```php
try {
    DB::beginTransaction();

    $post->save();
    $post->tags()->sync($tagIds);

    DB::commit();
} catch (Throwable $throwable) {
    DB::rollBack();

    throw $throwable;
}
```

Poderia ser `DB::transaction(fn () => ...)`, e é igualmente correto. Eu prefiro o bloco explícito porque deixa claro onde a transação começa e termina.

## Cuidado com efeitos fora do banco

E-mail, fila e chamadas HTTP não voltam atrás com rollback. Se um job é despachado dentro da transação e ela falha depois, o job roda com dados que não existem.

- Use `afterCommit` em jobs e listeners.
- Ou dispare o efeito depois do `commit`.

## Transações aninhadas

O Laravel usa savepoints, então uma Action que abre transação pode chamar outra que também abre. Só o `commit` mais externo grava de verdade.
