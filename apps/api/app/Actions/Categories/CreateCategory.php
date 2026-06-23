<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\User;

final class CreateCategory
{
    public function handle(StoreCategoryRequest $request, User $user): Category
    {
        return Category::query()->create([
            'user_id' => $user->getKey(),
            'name' => $request->validated('name'),
            'color' => $request->validated('color', '#6366f1'),
        ]);
    }
}
