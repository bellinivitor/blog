<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * The blog owner's account, with the login taken from BLOG_OWNER_EMAIL and
 * BLOG_OWNER_PASSWORD so no credential lives in the public repository.
 * Safe to run again: an existing account is left untouched, so a changed
 * password is never reset.
 */
class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('blog.owner.email');
        $password = config('blog.owner.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Set BLOG_OWNER_EMAIL and BLOG_OWNER_PASSWORD in .env before seeding the owner account.');
        }

        $owner = User::query()->firstOrNew(['email' => $email]);

        if ($owner->exists) {
            return;
        }

        $owner->forceFill([
            'name' => config('blog.author'),
            'password' => $password,
            'email_verified_at' => now(),
        ])->save();
    }
}
