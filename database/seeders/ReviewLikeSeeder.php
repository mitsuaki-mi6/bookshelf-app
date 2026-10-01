<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            // 各レビューに0〜3人のユーザーがいいね（自分のレビューを除く）
            $targetUsers = $users->reject(function ($user) use ($review) {
                return $user->id === $review->user_id;
            });

            if ($targetUsers->isNotEmpty()) {
                $likeCount = rand(0, min(3, $targetUsers->count()));
                if ($likeCount > 0) {
                    $likers = $targetUsers->random($likeCount);
                    foreach ($likers as $liker) {
                        $review->reviewLikes()->firstOrCreate([
                            'user_id' => $liker->id,
                        ]);
                    }
                }
            }
        }
    }
}
