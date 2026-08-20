<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        // 32件のレビューを配分して作成
        $comments = [
            'とても面白く、一気に読んでしまいました。',
            '学びが多く、何度も読み返したくなる一冊です。',
            '期待通りの内容で大満足でした。',
            '少し展開が難しかったですが、良書です。',
            '非常に考えさせられる深いテーマでした。',
        ];

        $count = 0;
        foreach ($books as $book) {
            // 各書籍に2〜4件のレビューを配分
            $reviewerCount = rand(2, 4);
            $selectedUsers = $users->random(min($reviewerCount, $users->count()));

            foreach ($selectedUsers as $user) {
                if ($count >= 32) break 2;

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => rand(3, 5), // 評価 3〜5
                    'comment' => $comments[array_rand($comments)],
                ]);
                $count++;
            }
        }
    }
}
