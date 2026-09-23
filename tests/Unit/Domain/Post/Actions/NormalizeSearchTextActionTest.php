<?php

use Domain\Post\Actions\NormalizeSearchTextAction;

it('removes accents and lowercases', function (string $input, string $expected) {
    expect((new NormalizeSearchTextAction)($input))->toBe($expected);
})->with([
    'portuguese accents' => ['Transações Ação Domínio', 'transacoes acao dominio'],
    'cedilla and tilde' => ['Ç Ã Õ', 'c a o'],
    'plain ascii' => ['Laravel 13', 'laravel 13'],
])->group('Unit', 'Post');
