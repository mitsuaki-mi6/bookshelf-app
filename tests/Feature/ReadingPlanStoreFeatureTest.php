<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use Tests\TestCase;

class ReadingPlanStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーが読書計画を正常に登録できること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 書籍を作成
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(), // 目標期日
        ]);

        /// Assert
        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans'); // リダイレクト先の確認

        // データベースに読書計画が登録されていることを確認
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーからのリクエストはログイン画面にリダイレクトされる()
    {
        $book = Book::factory()->create();

        // Act
        // 未認証でリクエスト
        $response = $this->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
        ]);

        /// Assert
        // ログイン画面へリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
