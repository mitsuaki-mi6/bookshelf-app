<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Tests\TestCase;

class ReadingPlanEditAuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーの場合読書計画編集画面が正常に表示()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 書籍を作成
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
            'target_date' =>  now()->addDays(5)->toDateString(),
        ]);

        // Act
        $response = $this->actingAs($user)->get("/reading-plans/{$readingPlan->id}/edit");

        /// Assert
        // 読書計画編集画面が正常に表示される
        $response->assertStatus(200);
        $response->assertSee('対象書籍');
        $response->assertSee('テスト書籍');
        $response->assertSee('進行中');
        // 目標期日が表示されることを確認
        $response->assertSee(now()->addDays(5)->toDateString());
    }

    /** @test */
    public function 未認証ユーザーからのリクエストはログイン画面にリダイレクトされる()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();
        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create();

        // Act
        // 未認証でリクエスト
        $response = $this->get("/reading-plans/{$readingPlan->id}/edit");

        /// Assert
        // ログイン画面へリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/login');;
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
            'target_date' =>  now()->addDays(5)->toDateString(),
        ]);

        // Act
        // 他人の読書計画にアクセス
        $response = $this->actingAs($myuser)->get("/reading-plans/{$readingPlan->id}/edit");

        // Assert
        $response->assertStatus(403);
    }
}
