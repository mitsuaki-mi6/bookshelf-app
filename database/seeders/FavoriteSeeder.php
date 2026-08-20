<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Favorite;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $user) {
            // 各ユーザーに3〜5冊のお気に入りをランダムに設定
            $favoriteBooks = $books->random(rand(3, min(5, $books->count())));

            foreach ($favoriteBooks as $book) {
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                ]);
            }
        }
    }
}
