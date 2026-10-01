<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーに紐づく複数の書籍が正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 紐づく書籍を3つ作成する
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // Act
        // ユーザーから書籍を取得する
        $results = $user->books;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($books as $index => $book) {
            $this->assertEquals($book->id, $results[$index]->id);
        }
    }

    /** @test */
    public function ユーザーに紐づく複数の書籍レビューが正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 紐づく書籍を3つ作成する
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 紐づくレビューを3つ作成する
        $reviews = Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'book_id' => $books->first()->id,
        ]);

        // Act
        // ユーザーからレビューを取得する
        $results = $user->reviews;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($reviews as $index => $review) {
            $this->assertEquals($review->id, $results[$index]->id);
        }
    }

    /** @test */
    public function ユーザーに紐づく複数の書籍お気に入りが正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 紐づく書籍を3つ作成する
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 3つの異なる本に対して、それぞれお気に入りを作成する
        $favorites = collect();
        foreach ($books as $book) {
            $favorites->push(Favorite::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]));
        }

        // Act
        // ユーザーからお気に入りを取得する
        $results = $user->favoriteBooks;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($favorites as $index => $favorite) {
            $this->assertEquals($favorite->id, $results[$index]->id);
        }
    }

    /** @test */
    public function ユーザーに紐づく複数のいいねが正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 紐づく書籍を3つ作成する
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 紐づくレビューを3つ作成する
        $reviews = Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'book_id' => $books->first()->id,
        ]);

        // 多対多のリレーション（likedReviews）を通じてレビューにいいねを紐付ける
        $user->likedReviews()->attach($reviews->pluck('id'));

        // Act
        // ユーザーからいいねしたレビュー（likedReviews）を取得する
        $results = $user->likedReviews;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($reviews as $index => $review) {
            $this->assertEquals($review->id, $results[$index]->id);
        }
    }
}
