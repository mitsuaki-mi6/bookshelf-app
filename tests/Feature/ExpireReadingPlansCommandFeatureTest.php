<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpireReadingPlansCommandFeatureTest extends TestCase
{
    use RefreshDatabase;

    // 事前の環境準備
    protected function setUp(): void
    {
        parent::setUp();
        // 基準日を固定（例: 2026-10-24 10:00:00）
        Carbon::setTestNow('2026-10-24 10:00:00');
    }

    // 各テストメソッド実行後のクリーンアップ
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function 読書計画のステータスが進行中で目標期日が処理日1日前以下のステータスを期限切れに自動更新()
    {
        // Arrange
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['user_id' => $user->id]);
        $book2 = Book::factory()->create(['user_id' => $user->id]);

        // 更新対象データ(複数件)
        $plan1 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => '2026-10-23',
            'status' => 'in_progress',
        ]);
        $plan2 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => '2026-10-22',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // expiredの更新を確認(複数権)
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan1->id,
            'book_id' => $book1->id,
            'status' => 'expired',
        ]);
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan2->id,
            'book_id' => $book2->id,
            'status' => 'expired',
        ]);
    }

    /** @test */
    public function 読書計画のステータスが進行中で目標期日が処理日のステータスに変更なし()
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 更新対象データ
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-24',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // ステータスの更新無しを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function 読書計画のステータスが進行中で目標期日が処理日＋1日のステータスに変更なし()
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 更新対象データ
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-25',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // ステータスの更新無しを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function 読書計画のステータスが完了で目標期日が処理日－1日のステータスに変更なし()
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 更新対象データ
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-23',
            'status' => 'completed',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // ステータスの更新無しを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'book_id' => $book->id,
            'status' => 'completed',
        ]);
    }
}
