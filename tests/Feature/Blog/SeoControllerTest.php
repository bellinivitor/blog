<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;

describe('sitemap', function () {
    test('lists the home, readings, published posts and tags with published posts', function () {
        $laravel = Tag::factory()->create(['slug' => 'laravel']);
        $draftOnly = Tag::factory()->create(['slug' => 'rascunho']);
        Post::factory()->published()->hasAttached($laravel)->create(['slug' => 'publicado']);
        Post::factory()->hasAttached($draftOnly)->create(['slug' => 'nao-publicado']);

        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('home').'</loc>', false)
            ->assertSee('<loc>'.route('blog.readings.index').'</loc>', false)
            ->assertSee('<loc>'.route('blog.posts.show', 'publicado').'</loc>', false)
            ->assertSee('<loc>'.route('blog.tags.show', 'laravel').'</loc>', false)
            ->assertDontSee('nao-publicado')
            ->assertDontSee(route('blog.tags.show', 'rascunho'));
    });

    test('is valid xml with one url per public page', function () {
        Post::factory()->published()->count(2)->create();

        $response = $this->get(route('sitemap'));

        $xml = simplexml_load_string($response->getContent());
        expect($xml)->not->toBeFalse();
        expect($xml->url)->toHaveCount(4);
    });
});

test('robots.txt points to the sitemap and disallows the admin', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.route('sitemap'));
});

test('private pages ask search engines not to index them', function (string $path) {
    $response = $this->get($path);

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
})->with([
    'login' => '/admin/login',
    'dashboard (redirects guests)' => '/admin',
    'admin posts (redirects guests)' => '/admin/posts',
]);

test('public blog pages stay indexable', function () {
    Post::factory()->published()->create(['slug' => 'publicado']);

    $this->get(route('home'))->assertHeaderMissing('X-Robots-Tag');
    $this->get(route('blog.posts.show', 'publicado'))->assertHeaderMissing('X-Robots-Tag');
    $this->get(route('sitemap'))->assertHeaderMissing('X-Robots-Tag');
});
