# Blog

Blog pessoal do Vitor Bellini sobre Laravel, Vue e arquitetura de software. É um app Laravel só com uma parte pública, onde os posts são lidos, e um painel em `/admin`, onde eles são escritos.

## O que ele faz

**Parte pública**

- Lista de posts na home e página de cada post em `/{slug}`, com o conteúdo em Markdown renderizado no servidor e código com syntax highlighting ([Phiki](https://github.com/phikiphp/phiki)).
- Posts relacionados e aviso de quando um post publicado foi revisado.
- Páginas por tag (`/tags/{slug}`) e busca por título e conteúdo (`/search`).
- Página de leituras recomendadas (`/leituras`), que os posts podem citar.
- Feed RSS (`/feed`), `sitemap.xml`, `robots.txt` e meta tags para SEO.
- Tema claro e escuro, sem piscar ao carregar a página.
- Contagem de visualizações sem cookies nem rastreamento: não conta bots nem o próprio autor, e conta cada leitor uma vez por post por dia. O salt que identifica o leitor é descartado todo dia (veja `/privacidade`).

**Painel (`/admin`)**

- Login com [Fortify](https://laravel.com/docs/fortify), incluindo autenticação em dois fatores. Não há cadastro público: a conta do autor é criada por seeder.
- CRUD de posts com editor Markdown ([Milkdown](https://milkdown.dev/)), upload de imagens, pré-visualização e fluxo de rascunho → publicado (e de volta).
- CRUD de tags e de leituras, com lixeira (soft delete) e restauração.
- Dashboard com estatísticas de leitura por dia.

## Stack

Laravel 13 · PHP 8.5 · Inertia v3 + Vue 3 + TypeScript · Tailwind CSS v4 · Fortify · Wayfinder · Pest · Pint · Larastan. Banco SQLite por padrão.

O código de negócio fica em `Domain/` (Actions, DTOs, QueryBuilders, Policies…), separado por domínio (`Post`, `Tag`, `Reading`). As convenções estão em [`docs/architecture/AGENTS.md`](docs/architecture/AGENTS.md).

## Rodando localmente

### Requisitos

- PHP 8.5 e Composer
- Node 22 e npm

### Instalação

```bash
git clone https://github.com/bellinivitor/blog.git
```

```bash
cd blog
```

O script `setup` instala as dependências, cria o `.env` a partir do `.env.example`, gera a `APP_KEY`, roda as migrations (criando o `database/database.sqlite`) e faz o build do front:

```bash
composer setup
```

Para as imagens enviadas pelo editor aparecerem, crie o link do storage:

```bash
php artisan storage:link
```

### Dados de exemplo (opcional)

Popula o banco com posts, tags e leituras de exemplo, além de um usuário `test@example.com` com senha `password`, que já dá acesso ao `/admin`:

```bash
php artisan db:seed
```

### Conta do autor

Para criar a conta de verdade, preencha `BLOG_OWNER_EMAIL` e `BLOG_OWNER_PASSWORD` no `.env` e rode:

```bash
php artisan db:seed --class=OwnerUserSeeder
```

Pode rodar de novo sem medo: se a conta já existe, ela não é alterada.

### Subindo o servidor

```bash
composer dev
```

Isso sobe juntos o servidor PHP, o worker da fila, os logs (Pail) e o Vite. O blog fica em <http://localhost:8000> e o painel em <http://localhost:8000/admin>.

Usando [Laravel Herd](https://herd.laravel.com/), o site já é servido em `http://blog.test` e basta rodar `npm run dev` para o Vite (ajuste o `APP_URL` no `.env`).

### Personalizando

Nome do autor, frase de destaque, bio, links e fuso horário das estatísticas ficam em [`config/blog.php`](config/blog.php).

## Testes e qualidade

```bash
composer test
```

Roda o Pint (estilo), o Larastan (análise estática) e a suíte Pest. Os testes usam SQLite em memória, então não tocam no seu banco local.

Para rodar tudo o que o CI roda, incluindo lint e checagem de tipos do front:

```bash
composer ci:check
```

## Produção

- Configure `APP_ENV=production`, `APP_DEBUG=false` e o `APP_URL` correto.
- Rode o scheduler (`php artisan schedule:run` a cada minuto): ele descarta o salt diário das visualizações.
- Rode um worker de fila (`php artisan queue:work`).
- O app confia no Cloudflare como proxy para pegar o IP e o esquema do visitante.

## Licença

MIT.
