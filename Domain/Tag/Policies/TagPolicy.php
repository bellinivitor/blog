<?php

namespace Domain\Tag\Policies;

use App\Models\Tag\Tag;
use App\Models\User;

/**
 * Tags are global to the blog: any authenticated user may manage them.
 */
class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tag $tag): bool
    {
        return true;
    }

    public function delete(User $user, Tag $tag): bool
    {
        return true;
    }

    public function restore(User $user, Tag $tag): bool
    {
        return true;
    }
}
