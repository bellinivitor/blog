<?php

namespace Domain\Post\Actions;

use Illuminate\Support\Str;

readonly class NormalizeSearchTextAction
{
    /**
     * Lowercase ASCII form used to match text regardless of accents and case
     * ("Domínio" and "dominio" both become "dominio").
     */
    public function __invoke(string $text): string
    {
        return Str::lower(Str::ascii($text));
    }
}
