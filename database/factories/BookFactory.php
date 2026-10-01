<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3), // 3単語程度の適当なタイトル
            'author' => fake()->name(), // 著者名（人名）
            'published_date' => fake()->date(),
            'isbn' => fake()->unique()->regexify('[0-9]{13}'), // 13桁の数字。重複無し。
            'description' => fake()->realText(100), // 100文字程度の説明文
            'image_url' => fake()->imageUrl(), // URL形式の文字列
        ];
    }
}
