<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewLike>
 */
class ReviewLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'review_id' => Review::factory(),
        ];
    }
}
