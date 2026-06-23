<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\UpdateCategory;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        /** @var User $user */
        $user = $request->user();

        return CategoryResource::collection(
            Category::query()->where('user_id', $user->getKey())->latest()->get(),
        );
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action): JsonResponse
    {
        $this->authorize('create', Category::class);

        /** @var User $user */
        $user = $request->user();

        return CategoryResource::make($action->handle($request, $user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategory $action,
    ): CategoryResource {
        $this->authorize('update', $category);

        return CategoryResource::make($action->handle($request, $category));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json(null, 204);
    }
}
