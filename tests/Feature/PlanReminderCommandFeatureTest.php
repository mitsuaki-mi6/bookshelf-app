<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlanReminderCommandFeatureTest extends TestCase
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

    // 共通に使用するデータの準備
    private function createUserAndBook()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        return [$user, $book];
    }

    /** @test */
    public function 読書計画のステータスが進行中でかつ目標期日が処理日の3日前のデータが正常に作成される()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 目標期日が処理日の3日後（2026-10-27）
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-27',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // 通知がデータベースに保存されているか確認
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);

        $notification = DB::table('notifications')->latest()->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('three_days_before', $data['timing']);
        $this->assertEquals('読書計画の期日が迫っています', $data['title']);
    }

    /** @test */
    public function 読書計画のステータスが進行中でかつ目標期日が処理日のデータが正常に作成される()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 目標期日が処理日当日（2026-10-24）
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-24',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // 通知がデータベースに保存されているか確認
        $notification = DB::table('notifications')->latest()->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('on_due_date', $data['timing']);
        $this->assertEquals('読書計画の期日です', $data['title']);
    }

    /** @test */
    public function 読書計画のステータスが進行中でかつ目標期日が処理日の3日後のデータが正常に作成される()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 目標期日が処理日の3日前（＝期限から3日後、2026-10-21）
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-21',
            'status' => 'expired',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert
        // 通知がデータベースに保存されているか確認
        $notification = DB::table('notifications')->latest()->first(); // 一番新しく追加された通知レコード
        $data = json_decode($notification->data, true);  // JSON形式から連想配列に変換

        $this->assertEquals('three_days_after', $data['timing']);
        $this->assertEquals('読書計画の期限が過ぎました', $data['title']);
    }

    /** @test */
    public function 読書計画のステータスが進行中でかつ目標期日が処理日の4日前のデータは作成されない()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 4日後のデータ（対象外）
        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-20',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert　データが作成されていないことを確認
        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function 読書計画のステータスが進行中でかつ目標期日が処理日の4日後のデータは作成されない()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 4日後のデータ（対象外）
        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-28',
            'status' => 'in_progress',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert　データが作成されていないことを確認
        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function 読書計画がゼロ件の場合はデータは作成されない()
    {
        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert　データが作成されていないことを確認
        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function 読書計画のステータスが進行中以外でかつ目標期日が処理日のデータは作成されない()
    {
        // Arrange
        [$user, $book] = $this->createUserAndBook();

        // 当日だがステータスが completed
        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-10-24',
            'status' => 'completed',
        ]);

        // Act コマンド実行
        Artisan::call('app:send-reading-plan-notifications');

        // Assert　データが作成されていないことを確認
        $this->assertDatabaseCount('notifications', 0);
    }
}
