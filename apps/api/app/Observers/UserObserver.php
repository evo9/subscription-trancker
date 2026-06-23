<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Models\User;

class UserObserver
{
    /** @var list<array{name: string, color: string}> */
    private const DEFAULT_CATEGORIES = [
        ['name' => 'Streaming', 'color' => '#E50914'],
        ['name' => 'Software', 'color' => '#0078D4'],
        ['name' => 'Hosting', 'color' => '#FF9900'],
        ['name' => 'Fitness', 'color' => '#00B140'],
        ['name' => 'Other', 'color' => '#6B7280'],
    ];

    public function created(User $user): void
    {
        foreach (self::DEFAULT_CATEGORIES as $category) {
            Category::query()->create([
                'user_id' => $user->id,
                'name' => $category['name'],
                'color' => $category['color'],
            ]);
        }
    }
}
