<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Tests\TestCase;

class ReadingPlanIndexFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自身の読書計画一覧にアクセスし正常に表示されること()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'title' => '書籍進行中',
        ]);
        $book2 = Book::factory()->create([
            'title' => '書籍期限切れ',
        ]);
        $book3 = Book::factory()->create([
            'title' => '書籍完了',
        ]);

        // 読書計画を作成
        // 1番目に作成（id = 1 相当）
        $readingPlan1 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'in_progress',
        ]);
        // 2番目に作成（id = 2 相当）
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'expired',
        ]);
        // 3番目に作成（id = 3 相当）
        $readingPlan3 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Act: 認証済みユーザーで読書計画一覧にアクセス
        $response = $this->actingAs($user)->get('/reading-plans');

        // Assert　
        // 読書計画一覧が正常に表示される
        $response->assertStatus(200);
        $response->assertSee('すべて');  // 選択肢が「すべて」になっていることを確認
        $response->assertSee('新規計画作成');  // 新規計画作成ボタンの存在を確認
        $response->assertSeeInOrder(['書籍進行中', '書籍期限切れ', '書籍完了']);  // 書籍タイトルが順番通りに表示されることを確認
        $response->assertSeeInOrder(['書籍進行中', '進行中', '読了する', '編集', '削除']);  // 書籍進行中の読書計画の詳細が順番通りに表示されることを確認
        $response->assertSeeInOrder(['書籍期限切れ', '期限切れ', '読了する', '編集', '削除']);  // 書籍期限切れの読書計画の詳細が順番通りに表示されることを確認
        $response->assertSeeInOrder(['書籍完了', '完了', '削除']); // 書籍完了の読書計画の詳細が順番通りに表示されることを確認
    }

    /** @test */
    public function 状態の選択で絞り込みができること()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'title' => '書籍進行中',
        ]);
        $book2 = Book::factory()->create([
            'title' => '書籍期限切れ',
        ]);
        $book3 = Book::factory()->create([
            'title' => '書籍完了',
        ]);

        // 読書計画を作成
        // 1番目に作成（id = 1 相当）
        $readingPlan1 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'in_progress',
        ]);
        // 2番目に作成（id = 2 相当）
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'expired',
        ]);
        // 3番目に作成（id = 3 相当）
        $readingPlan3 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Act: 認証済みユーザーで読書計画一覧にアクセス
        $response = $this->actingAs($user)->get('reading-plans?status=in_progress');

        // Assert　
        // 読書計画一覧が正常に表示される
        $response->assertStatus(200);
        $response->assertSee('進行中');  // 選択肢が「進行中」になっていることを確認
        $response->assertSee('書籍進行中');  // 書籍進行中のみ表示を確認
        $response->assertDontSee('書籍期限切れ');  // 書籍期限切れは表示されないことを確認
        $response->assertDontSee('書籍完了');  // 書籍完了は表示されないことを確認
    }

    /** @test */
    public function テスト計画がゼロ件()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // 書籍データを作成
        $book = Book::factory()->create();

        // Act: 認証済みユーザーで読書計画一覧にアクセス
        $response = $this->actingAs($user)->get('/reading-plans');

        // Assert　
        // 読書計画無しの場合は指定のメッセージが表示される
        $response->assertStatus(200);
        $response->assertSee('該当する読書計画はありません。');
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Act: 未認証ユーザーで読書計画一覧にアクセス
        $response = $this->get('/reading-plans');

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
