<?php

use App\Models\Post\Post;
use App\Models\Reading\Reading;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

function renderedContent(TestResponse $response): string
{
    $content = '';
    $response->assertInertia(function (Assert $page) use (&$content) {
        $content = $page->toArray()['props']['content'];
    });

    return $content;
}

test('links a cited reading to its URL in a new tab', function () {
    $reading = Reading::factory()->create(['url' => 'https://martinfowler.com/books/refactoring.html']);
    Post::factory()->published()->create(['slug' => 'hello', 'content' => "Leia [o livro do Fowler](leitura:{$reading->id}) antes."]);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    expect($content)->toContain('<a target="_blank" rel="noopener" href="https://martinfowler.com/books/refactoring.html">o livro do Fowler</a>');
});

test('keeps only the text when the cited reading is trashed or missing', function () {
    $trashed = Reading::factory()->trashed()->create(['url' => 'https://example.com/gone']);
    Post::factory()->published()->create([
        'slug' => 'hello',
        'content' => "Um [livro *antigo*](leitura:{$trashed->id}) e um [inexistente](leitura:999).",
    ]);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    expect($content)
        ->toContain('Um livro <em>antigo</em> e um inexistente.')
        ->not->toContain('<a ')
        ->not->toContain('leitura:');
});

test('follows a change of the reading URL on the next render', function () {
    $reading = Reading::factory()->create(['url' => 'https://example.com/old']);
    Post::factory()->published()->create(['slug' => 'hello', 'content' => "Veja [isto](leitura:{$reading->id})."]);
    $this->get(route('blog.posts.show', 'hello'));

    $this->travel(1)->minute();
    $reading->update(['url' => 'https://example.com/new']);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    expect($content)->toContain('href="https://example.com/new"');
});

test('leaves ordinary links untouched', function () {
    Post::factory()->published()->create(['slug' => 'hello', 'content' => 'Veja [a doc](https://laravel.com/docs).']);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    expect($content)->toContain('<a href="https://laravel.com/docs">a doc</a>');
});
