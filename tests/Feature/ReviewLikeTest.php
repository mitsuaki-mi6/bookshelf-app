<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーのいいねでいいねが登録される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act
        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        // Assert:
        $response->assertStatus(302);
        // データベースにいいねが登録されていることを確認
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーのいいね解除でいいねが削除される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // いいねを作成
        $review_like = ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // Act
        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        // Assert:
        $response->assertStatus(302);
        // データベースからいいねが削除されていることを確認
        $this->assertDatabaseMissing('review_likes', [
            'id' => $review_like->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーでいいね登録または解除にアクセス時ログイン画面にリダイレクト()
    {
        // Act
        $response = $this->post("/reviews/1/like");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
