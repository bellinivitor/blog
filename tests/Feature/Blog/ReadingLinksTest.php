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

test('links a cited reading to it on the readings page, in a new tab', function () {
    $reading = Reading::factory()->create(['url' => 'https://martinfowler.com/books/refactoring.html']);
    Post::factory()->published()->create(['slug' => 'hello', 'content' => "Leia [o livro do Fowler](leitura:{$reading->id}) antes."]);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    $href = route('blog.readings.index')."#leitura-{$reading->id}";
    expect($content)
        ->toContain("<a target=\"_blank\" rel=\"noopener\" href=\"{$href}\">o livro do Fowler</a>")
        ->not->toContain('martinfowler.com');
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

test('brings the link back on the next render when the reading is restored', function () {
    $reading = Reading::factory()->trashed()->create();
    Post::factory()->published()->create(['slug' => 'hello', 'content' => "Veja [isto](leitura:{$reading->id})."]);
    expect(renderedContent($this->get(route('blog.posts.show', 'hello'))))->not->toContain('<a ');

    $this->travel(1)->minute();
    $reading->restore();

    expect(renderedContent($this->get(route('blog.posts.show', 'hello'))))
        ->toContain('href="'.route('blog.readings.index')."#leitura-{$reading->id}\"");
});

test('leaves ordinary links untouched', function () {
    Post::factory()->published()->create(['slug' => 'hello', 'content' => 'Veja [a doc](https://laravel.com/docs).']);

    $content = renderedContent($this->get(route('blog.posts.show', 'hello')));

    expect($content)->toContain('<a href="https://laravel.com/docs">a doc</a>');
});
