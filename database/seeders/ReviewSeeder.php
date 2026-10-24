<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        // レビューのテンプレート
        $reviewsTemplates = [
            5 => [
                '素晴らしい本でした！',
                '人生が変わりました。',
                '何度も読み返しています。',
            ],
            4 => [
                'とても参考になりました。',
                '読みやすくておすすめです。',
                '期待通りの内容でした。',
            ],
            3 => [
                '普通でした。',
                '可もなく不可もなく。',
                '期待したほどではなかった。',
            ],
            2 => [
                '少し期待外れでした。',
                '内容が薄い印象。',
                'もう少し深掘りしてほしかった。',
            ],
            1 => [
                '残念ながら合いませんでした。',
                '期待と違いました。',
            ],
        ];

        $count = 0;
        foreach ($books as $book) {
            // 各書籍に2〜4件のレビューを配分
            $reviewerCount = rand(2, 4);
            $selectedUsers = $users->random(min($reviewerCount, $users->count()));

            foreach ($selectedUsers as $user) {
                if ($count >= 32) {
                    break 2;
                }

                $rating = rand(1, 5); // 評価 1〜5

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $reviewsTemplates[$rating][array_rand($reviewsTemplates[$rating])], // 決定した評価に対応するコメントを選ぶ
                ]);
                $count++;
            }
        }
    }
}
