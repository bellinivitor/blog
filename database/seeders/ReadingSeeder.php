<?php

namespace Database\Seeders;

use App\Models\Reading\Reading;
use Illuminate\Database\Seeder;

/**
 * Example readings for development, oldest first. Safe to run again:
 * readings are matched by URL and updated instead of duplicated.
 */
class ReadingSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, url: string}>
     */
    private const array READINGS = [
        ['title' => 'The Twelve-Factor App', 'url' => 'https://12factor.net/'],
        ['title' => 'Refactoring, de Martin Fowler', 'url' => 'https://martinfowler.com/books/refactoring.html'],
        ['title' => 'Domain-Driven Design, de Eric Evans', 'url' => 'https://www.domainlanguage.com/ddd/'],
        ['title' => 'The Pragmatic Programmer', 'url' => 'https://pragprog.com/titles/tpp20/the-pragmatic-programmer-20th-anniversary-edition/'],
        ['title' => 'A Philosophy of Software Design, de John Ousterhout', 'url' => 'https://web.stanford.edu/~ouster/cgi-bin/book.php'],
        ['title' => 'Parse, don’t validate', 'url' => 'https://lexi-lambda.github.io/blog/2019/11/05/parse-don-t-validate/'],
        ['title' => 'Test Desiderata, de Kent Beck', 'url' => 'https://kentbeck.github.io/TestDesiderata/'],
        ['title' => 'Laravel Beyond CRUD', 'url' => 'https://laravel-beyond-crud.com/'],
    ];

    public function run(): void
    {
        foreach (self::READINGS as $index => $reading) {
            $model = Reading::query()->withTrashed()->firstOrNew(['url' => $reading['url']]);
            $model->title = $reading['title'];
            // One day apart, so "newest first" has a stable, visible order.
            $model->created_at = now()->subDays(count(self::READINGS) - $index);
            $model->deleted_at = null;
            $model->save();
        }
    }
}
