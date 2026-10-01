<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍から複数の紐づくジャンル正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 書籍を1つ作成し、
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        // それに紐づくジャンルを3つ作成する
        $genres = Genre::factory()->count(3)->create();

        // 中間テーブルや外部キーなどを介してジャンルと書籍を紐付ける
        $book->genres()->attach($genres->pluck('id'));

        // Act
        // 書籍のリレーションをジャンルを取得する
        $results = $book->genres;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($genres as $index => $genre) {
            $this->assertEquals($genre->id, $results[$index]->id);
        }
    }

    /** @test */
    public function 書籍から複数の紐づくレビューが正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 書籍を1つ作成し、
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        // それに紐づくレビューを3つ作成する
        $reviews = Review::factory()->count(3)->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // Act
        // 書籍のリレーションをレビューを取得する
        $results = $book->reviews;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($reviews as $index => $review) {
            $this->assertEquals($review->id, $results[$index]->id);
        }
    }

    /** @test */
    public function 書籍から紐づく複数のお気に入りが正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // 書籍を1つ作成し、
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        // それに紐づくお気に入りを3つ作成する
        $favorites = Favorite::factory()->count(3)->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // Act
        // 書籍のリレーションをお気に入りを取得する
        $results = $book->favorites;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($favorites as $index => $favorite) {
            $this->assertEquals($favorite->id, $results[$index]->id);
        }
    }
}
