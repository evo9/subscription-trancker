<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    /** @var list<string> */
    private const NAMES = [
        'Streaming', 'Software', 'Hosting', 'Fitness', 'Other',
        'Music', 'Cloud Storage', 'Gaming', 'Education', 'Utilities',
    ];

    /** @var list<string> */
    private const COLORS = [
        '#E50914', '#0078D4', '#FF9900', '#00B140', '#6B7280',
        '#1DB954', '#4285F4', '#A855F7', '#F59E0B', '#64748B',
    ];

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $index = fake()->numberBetween(0, count(self::NAMES) - 1);

        return [
            'user_id' => User::factory(),
            'name' => self::NAMES[$index],
            'color' => self::COLORS[$index],
        ];
    }
}
