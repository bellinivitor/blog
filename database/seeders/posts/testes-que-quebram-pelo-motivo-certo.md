Um teste útil falha quando o comportamento muda, e só nessa hora. Se ele quebra a cada refatoração, está testando a implementação.

## Teste o que o usuário vê

Para uma rota, isso quer dizer a resposta e o estado do banco:

```php
test('creates a draft post owned by the author', function () {
    $author = User::factory()->create();

    $response = $this->actingAs($author)->post(route('posts.store'), [
        'title' => 'Hello World',
        'content' => '# Hello',
    ]);

    $post = Post::query()->sole();
    $response->assertRedirect(route('posts.edit', $post));
    expect($post->status)->toBe(PostStatus::Draft);
});
```

## Três hábitos que ajudam

1. Um teste, um comportamento. O nome diz qual.
2. Valores esperados escritos à mão, nunca calculados com a mesma lógica do código.
3. Datasets quando só a entrada muda.

### Sobre cobertura

Cobertura alta com asserts fracos é pior do que cobertura média com asserts bons, porque dá uma confiança que não existe.

> O melhor teste é o que te avisa de um bug que você não sabia que existia.
