<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\Enums\PostStatus;

test('a post page renders its title, description and article open graph tags', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Post::factory()->hasAttached($tag)->create([
        'title' => 'Transações que não mentem',
        'slug' => 'transacoes',
        'excerpt' => 'Rollback no banco, e os efeitos que ficam de fora dele.',
        'status' => PostStatus::Published,
        'published_at' => '2025-12-03 10:00:00',
    ]);

    $response = $this->get(route('blog.posts.show', 'transacoes'));

    $response
        ->assertSee('<title>Transações que não mentem - '.config('app.name').'</title>', false)
        ->assertSee('<meta name="description" content="Rollback no banco, e os efeitos que ficam de fora dele.">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.posts.show', 'transacoes').'">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('<meta property="og:title" content="Transações que não mentem">', false)
        ->assertSee('<meta property="article:published_time" content="2025-12-03T10:00:00+00:00">', false)
        ->assertSee('<meta property="article:tag" content="Laravel">', false)
        ->assertSee('<meta name="twitter:card" content="summary">', false);
});

test('a post without excerpt describes itself with the start of its text', function () {
    Post::factory()->published()->create([
        'slug' => 'sem-resumo',
        'excerpt' => null,
        'content' => "## Contexto\n\nUma escrita que toca **duas tabelas** sem transação funciona quase sempre.",
    ]);

    $response = $this->get(route('blog.posts.show', 'sem-resumo'));

    $response->assertSee('<meta name="description" content="Contexto Uma escrita que toca duas tabelas sem transação funciona quase sempre.">', false);
});

test('meta content is escaped', function () {
    Post::factory()->published()->create([
        'slug' => 'aspas',
        'title' => 'Um "título" <com> tags',
    ]);

    $response = $this->get(route('blog.posts.show', 'aspas'));

    $response->assertSee('<meta property="og:title" content="Um &quot;título&quot; &lt;com&gt; tags">', false);
});

test('the home and tag pages describe themselves as websites', function () {
    Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

    $this->get(route('home'))
        ->assertSee('<title>'.config('blog.author').'</title>', false)
        ->assertSee('<meta property="og:title" content="'.config('blog.author').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false);

    $this->get(route('blog.tags.show', 'vue'))
        ->assertSee('<meta property="og:title" content="Vue">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.tags.show', 'vue').'">', false);
});

test('admin pages render no open graph tags', function () {
    $this->get(route('login'))->assertDontSee('og:title', false);
});
