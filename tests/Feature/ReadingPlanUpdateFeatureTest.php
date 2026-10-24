<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanUpdateFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーで読書計画を正常に編集できること()
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
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        // Act
        $response = $this->actingAs($user)->put("/reading-plans/{$readingPlan->id}", [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(10)->toDateString(),
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans'); // リダイレクト先の確認

        // データベースに読書計画が更新されていることを確認
        $this->assertEquals(
            now()->addDays(10)->toDateString(),
            $readingPlan->fresh()->target_date->toDateString()
        );
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
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        // Act
        // 未認証でリクエスト
        $response = $this->put("/reading-plans/{$readingPlan->id}", [
            'book_id' => $book->id,
            'target_date' => now()->addDays(10)->toDateString(),
        ]);

        // Assert
        // ログイン画面へリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 他人の読書計画にアクセスの場合は403エラー()
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
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        // Act
        // 他人の読書計画を更新しようとリクエスト
        $response = $this->actingAs($myuser)->put("/reading-plans/{$readingPlan->id}", [
            'target_date' => now()->addDays(10)->toDateString(),
        ]);

        // Assert
        $response->assertStatus(403);
    }
}
