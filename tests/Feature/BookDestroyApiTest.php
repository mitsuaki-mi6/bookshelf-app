<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\ReviewLike;

class BookDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 指定IDの書籍と関連テーブルを削除する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // レビューを作成
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
        ]);

        // いいねを作成
        ReviewLike::factory()->create([
            'review_id' => $review->id,
            'user_id' => $user->id, // ログイン中のユーザーがいいね（ON状態のテスト）
        ]);

        // お気に入りを作成
        $favorite = Favorite::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
        ]);

        // Act  編集データ
        $response = $this->actingAs($user)->delete("/api/v1/books/{$book->id}");

        // Assert
        $response->assertStatus(200);

        // 書籍の論理削除（deleted_atに日時が入る）を確認
        $this->assertNotNull($book->fresh()->deleted_at);

        // 中間テーブル（book_genre）からデータが削除されていることを確認
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // お気に入りが削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);

        // レビューが削除されていることを確認
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        // レビューのいいねが削除されていることを確認
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 存在しない書籍ID指定された場合（404）()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ（存在しないID '9999' を指定）
        $response = $this->actingAs($user)->delete('/api/v1/books/9999',);

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'message' => '指定された書籍が見つかりませんでした。',
        ]);
    }
}
