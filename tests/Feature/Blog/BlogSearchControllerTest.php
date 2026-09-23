<?php

use App\Models\Post\Post;

test('finds published posts by title or content, title matches first', function () {
    Post::factory()->published()->create([
        'title' => 'Filas no Laravel',
        'slug' => 'filas',
        'content' => 'Texto sem o termo.',
        'published_at' => '2026-01-01 10:00:00',
    ]);
    Post::factory()->published()->create([
        'title' => 'Outro assunto',
        'slug' => 'outro',
        'content' => 'Aqui eu falo de filas e jobs.',
        'published_at' => '2026-05-01 10:00:00',
    ]);
    Post::factory()->published()->create(['title' => 'Nada a ver', 'content' => 'Vue e Tailwind.']);

    $response = $this->getJson(route('blog.search', ['q' => 'filas']));

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.slug', 'filas')
        ->assertJsonPath('1.slug', 'outro');
});

test('returns a plain text snippet around the term', function () {
    Post::factory()->published()->create([
        'title' => 'Transações',
        'content' => "## Cuidado\n\nE-mail, **fila** e chamadas HTTP não voltam atrás com rollback.",
    ]);

    $response = $this->getJson(route('blog.search', ['q' => 'rollback']));

    $response->assertJsonPath('0.snippet', 'Cuidado E-mail, fila e chamadas HTTP não voltam atrás com rollback.');
});

test('does not return posts that are not public', function () {
    Post::factory()->create(['title' => 'Rascunho secreto']);
    Post::factory()->published()->trashed()->create(['title' => 'Apagado secreto']);

    $response = $this->getJson(route('blog.search', ['q' => 'secreto']));

    $response->assertOk()->assertExactJson([]);
});

test('matches wildcard characters literally', function () {
    Post::factory()->published()->create(['title' => 'Cem por cento', 'content' => 'Nada aqui.']);
    Post::factory()->published()->create(['title' => 'Desconto de 50%off', 'content' => 'Texto.']);

    $response = $this->getJson(route('blog.search', ['q' => '%o']));

    $response->assertJsonCount(1)->assertJsonPath('0.title', 'Desconto de 50%off');
});

test('requires at least two characters', function () {
    $response = $this->getJson(route('blog.search', ['q' => 'a']));

    $response->assertInvalid(['q' => 'The q field must be at least 2 characters.']);
});

test('ignores accents and case in both the term and the posts', function (string $term) {
    Post::factory()->published()->create(['title' => 'Separando por domínio', 'content' => 'Texto.']);
    Post::factory()->published()->create(['title' => 'Outro', 'content' => 'Uma Ação que não depende do HTTP.']);

    $response = $this->getJson(route('blog.search', ['q' => $term]));

    $response->assertOk()->assertJsonCount(1);
})->with([
    'term without accent' => 'dominio',
    'uppercase with accent' => 'DOMÍNIO',
    'accented word in content' => 'acao que nao',
]);

test('builds the snippet around an accented match', function () {
    Post::factory()->published()->create([
        'title' => 'Outro',
        'content' => 'Primeiro parágrafo. Depois falamos de transações aninhadas e savepoints.',
    ]);

    $response = $this->getJson(route('blog.search', ['q' => 'transacoes']));

    $response->assertJsonPath('0.snippet', 'Primeiro parágrafo. Depois falamos de transações aninhadas e savepoints.');
});
