<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public blog content
    |--------------------------------------------------------------------------
    |
    | Texts and links shown on the public blog pages. Edit freely; nothing
    | here is secret.
    |
    */

    'author' => 'Vitor Bellini',

    'headline' => 'Escrevo sobre o que aprendo construindo software.',

    'bio' => 'Sou desenvolvedor e passo a maior parte do tempo entre Laravel, Vue e decisões de arquitetura. Aqui ficam notas, erros e o que funcionou.',

    /*
    | Timezone that decides which day a view belongs to in the reading stats.
    */

    'timezone' => 'America/Sao_Paulo',

    'links' => [
        ['label' => 'GitHub', 'url' => 'https://github.com/bellinivitor'],
        ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/bellinivitor/'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner account
    |--------------------------------------------------------------------------
    |
    | Login used by OwnerUserSeeder to create the author's account. Unlike
    | the texts above, these are credentials: they only come from the
    | environment and must never be committed.
    |
    */

    'owner' => [
        'email' => env('BLOG_OWNER_EMAIL'),
        'password' => env('BLOG_OWNER_PASSWORD'),
    ],

];
