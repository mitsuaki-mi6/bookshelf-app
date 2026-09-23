<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class ReviewOperationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証認可済のユーザでレビューが正しく更新されること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => '既存のテストレビュー',
        ]);

        // Act
        $response = $this->actingAs($user)->put("/reviews/{$Review->id}", [
            'rating' => 5,
            'comment' => '更新テスト評価コメント',
        ]);

        // Assert
        $response->assertStatus(302);

        // レビューが登録されていることを確認
        $this->assertDatabaseHas('reviews', [
            'rating' => 5,
            'comment' => '更新テスト評価コメント',
        ]);
    }

    /** @test */
    public function 認証済で他人のIDでレビュー更新時は403エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => '既存のテストレビュー',
        ]);

        // Act
        $response = $this->actingAs($otherUser)->put("/reviews/{$Review->id}", [
            'rating' => 5,
            'comment' => '更新テスト評価コメント',
        ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function 認証認可済のユーザでレビューが削除できること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // Act
        $response = $this->actingAs($user)->delete("/reviews/{$Review->id}");

        // Assert
        $response->assertStatus(302);
    }

    /** @test */
    public function 認証済で他人のIDでレビュー削除時は403エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // Act
        $response = $this->actingAs($otherUser)->delete("/reviews/{$Review->id}");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function 未認証ユーザーでレビュー更新しようとした場合ログイン画面にリダイレクト()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();
        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create();

        // Act: 未認証ユーザーでレビュー更新にアクセス
        $response = $this->put("/reviews/{$Review->id}");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーでレビュー削除しようとした場合ログイン画面にリダイレクト()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();
        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create();

        // Act: 未認証ユーザーでレビュー削除にアクセス
        $response = $this->delete("/reviews/{$Review->id}");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
