<?php

namespace Database\Seeders;

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use App\Models\User;
use Domain\Post\Enums\PostStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Example posts for development: eight published (2025–2026) and two drafts.
 * Each body lives in database/seeders/posts/{slug}.md. Safe to run again:
 * posts and tags are matched by slug and updated instead of duplicated.
 */
class PostSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, excerpt: string|null, published_at: string|null, tags: array<int, string>}>
     */
    private const array POSTS = [
        [
            'title' => 'Actions no lugar de services no Laravel',
            'excerpt' => 'Por que troquei classes de serviço por Actions de uma responsabilidade.',
            'published_at' => '2026-09-18 10:00:00',
            'tags' => ['Laravel', 'Arquitetura'],
        ],
        [
            'title' => 'QueryBuilders customizados em vez de Repository',
            'excerpt' => 'O Eloquent já é a abstração; o que falta é dar nome às consultas.',
            'published_at' => '2026-08-30 10:00:00',
            'tags' => ['Laravel', 'Arquitetura'],
        ],
        [
            'title' => 'Formulários com o componente Form do Inertia 3',
            'excerpt' => null,
            'published_at' => '2026-07-12 10:00:00',
            'tags' => ['Vue', 'Inertia'],
        ],
        [
            'title' => 'Testes que quebram pelo motivo certo',
            'excerpt' => 'Como escrevo testes que falham quando o comportamento muda, e só então.',
            'published_at' => '2026-06-04 10:00:00',
            'tags' => ['Testes', 'Laravel'],
        ],
        [
            'title' => 'Enums do PHP com comportamento',
            'excerpt' => 'Backed enums no banco, na validação e onde eles param de bastar.',
            'published_at' => '2026-04-15 10:00:00',
            'tags' => ['PHP'],
        ],
        [
            'title' => 'Dark mode sem flash com Tailwind 4',
            'excerpt' => 'Um cookie, uma classe no html e nenhum piscar de tela.',
            'published_at' => '2026-02-21 10:00:00',
            'tags' => ['CSS'],
        ],
        [
            'title' => 'Transações que não mentem',
            'excerpt' => 'Rollback no banco, e os efeitos que ficam de fora dele.',
            'published_at' => '2025-12-03 10:00:00',
            'tags' => ['Laravel'],
        ],
        [
            'title' => 'O que aprendi migrando um monólito para domínios',
            'excerpt' => 'Três erros que cometi separando o código por domínio, e como desfiz cada um.',
            'published_at' => '2025-09-10 10:00:00',
            'tags' => ['Arquitetura'],
        ],
        [
            'title' => 'Filas no Laravel: quando vale a pena',
            'excerpt' => null,
            'published_at' => null,
            'tags' => ['Laravel'],
        ],
        [
            'title' => 'Tipando props do Inertia com TypeScript',
            'excerpt' => null,
            'published_at' => null,
            'tags' => ['TypeScript', 'Inertia'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->oldest('id')->first() ?? User::factory()->create();

        foreach (self::POSTS as $data) {
            $slug = Str::slug($data['title']);

            $post = Post::query()->withTrashed()->firstOrNew(['slug' => $slug]);
            $post->forceFill([
                'author_id' => $author->id,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'content' => File::get(database_path("seeders/posts/{$slug}.md")),
                'status' => $data['published_at'] ? PostStatus::Published : PostStatus::Draft,
                'published_at' => $data['published_at'],
                'deleted_at' => null,
            ])->save();

            $post->tags()->sync(array_map($this->tagId(...), $data['tags']));
        }
    }

    private function tagId(string $name): int
    {
        $tag = Tag::query()->withTrashed()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        );

        if ($tag->trashed()) {
            $tag->restore();
        }

        return $tag->id;
    }
}
