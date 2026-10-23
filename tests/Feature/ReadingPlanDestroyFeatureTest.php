<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Tests\TestCase;

class ReadingPlanDestroyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーで削除クリック時正常に削除される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 書籍を作成
        $book = Book::factory()->create();
        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
            'target_date' =>  now()->addDays(5)->toDateString(),
        ]);

        // Act
        $response = $this->actingAs($user)->delete("/reading-plans/{$readingPlan->id}");

        /// Assert
        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans'); // リダイレクト先の確認

        // 読書計画が削除されていることを確認
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーからのリクエストはログイン画面にリダイレクトされる()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 書籍を作成
        $book = Book::factory()->create();
        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
            'target_date' =>  now()->addDays(5)->toDateString(),
        ]);

        // Act
        // 未認証でリクエスト
        $response = $this->delete("/reading-plans/{$readingPlan->id}");

        /// Assert
        // ログイン画面へリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 他人の読書計画を削除クリックの場合は403エラー()
    {
        // Arrange
        // ユーザーを作成
        $myuser = User::factory()->create();  // 自分
        $otheruser = User::factory()->create();  // 他人
        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画を作成(他人)
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otheruser->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
            'target_date' =>  now()->addDays(5)->toDateString(),
        ]);

        // Act
        $response = $this->actingAs($myuser)->delete("/reading-plans/{$readingPlan->id}");

        // Assert
        $response->assertStatus(403);
    }
}
