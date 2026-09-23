<?php

namespace Domain\Post\Policies;

use App\Models\Post\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $this->isAuthor($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->isAuthor($user, $post);
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->isAuthor($user, $post);
    }

    private function isAuthor(User $user, Post $post): bool
    {
        return $user->id === $post->author_id;
    }
}
