<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 指定idの書籍と関連テーブルを削除する()
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

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
        ]);

        // Act  編集データ
        $response = $this->actingAs($user, 'sanctum')->delete("/api/v1/books/{$book->id}");

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

        // 読書計画が削除されていることを確認
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /** @test */
    public function 存在しない書籍id指定された場合（404）()
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
        $response = $this->actingAs($user, 'sanctum')->delete('/api/v1/books/9999');

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'message' => '指定された書籍が見つかりませんでした。',
        ]);
    }

    /** @test */
    public function 未認証ユーザーで書籍削除しようとした場合401()
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

        // Act  編集データ（未認証ユーザーを指定）
        $response = $this->delete("/api/v1/books/{$book->id}");

        // Assert
        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証されていません。',
        ]);
    }

    /** @test */
    public function 他人の書籍を削除の場合は403()
    {
        // Arrange
        // ユーザーを作成
        $myuser = User::factory()->create();
        $otheruser = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otheruser->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  他人の書籍を指定
        $response = $this->actingAs($myuser, 'sanctum')->delete("/api/v1/books/{$book->id}");

        // Assert
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'この操作は許可されていません。',
        ]);
    }
}
