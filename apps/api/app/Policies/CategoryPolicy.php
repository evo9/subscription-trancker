<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

final class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Category $category): bool
    {
        return $user->getKey() === $category->user_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->getKey() === $category->user_id;
    }
}
