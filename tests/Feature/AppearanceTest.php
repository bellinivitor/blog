<?php

test('pages render in the light theme when no theme was chosen', function () {
    $this->get(route('home'))
        ->assertSee("const appearance = 'light';", false)
        ->assertDontSee('class="dark"', false);
});

test('pages keep the theme chosen in the appearance cookie', function (string $appearance) {
    $this->withUnencryptedCookie('appearance', $appearance)
        ->get(route('home'))
        ->assertSee("const appearance = '{$appearance}';", false);
})->with(['dark', 'system']);
