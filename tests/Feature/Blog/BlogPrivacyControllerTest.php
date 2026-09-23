<?php

use Inertia\Testing\AssertableInertia as Assert;

test('shows the privacy page with the real session cookie name', function () {
    config(['session.cookie' => 'blog-session']);

    $response = $this->get(route('blog.privacy'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('blog/Privacy')
        ->where('sessionCookie', 'blog-session')
    );
});
