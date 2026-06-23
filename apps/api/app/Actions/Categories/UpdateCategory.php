<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

final class UpdateCategory
{
    public function handle(UpdateCategoryRequest $request, Category $category): Category
    {
        $category->update($request->validated());

        return $category->fresh() ?? $category;
    }
}
