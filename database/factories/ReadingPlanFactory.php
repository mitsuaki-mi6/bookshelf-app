<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['in_progress', 'completed', 'expired']); //  進行中、完了、期限切れ

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'), // 過去〜未来の期日
            'status' => $status,
            'completed_at' => $status === 'completed' ? fake()->date() : null, // 完了のときだけ日付を入れる
        ];
    }
}
