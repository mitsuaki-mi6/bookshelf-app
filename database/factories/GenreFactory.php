<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(), // 単一の重複しない単語
        ];
    }
}
