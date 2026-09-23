<?php

namespace Domain\Reading\Policies;

use App\Models\Reading\Reading;
use App\Models\User;

/**
 * Readings are global to the blog: any authenticated user may manage them, like tags.
 */
class ReadingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Reading $reading): bool
    {
        return true;
    }

    public function delete(User $user, Reading $reading): bool
    {
        return true;
    }

    public function restore(User $user, Reading $reading): bool
    {
        return true;
    }
}
