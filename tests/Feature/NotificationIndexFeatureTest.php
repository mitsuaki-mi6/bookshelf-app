<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationIndexFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーで自身の通知一覧が正常に表示される（未読）()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 通知テーブルデータ作成
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\PlanReminderNotification',
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書計画の期日が迫っています',
                'body' => '書籍「テスト書籍」の読書計画の期日が3日後に迫っています。',
                'timing' => 'three_days_before',
            ],
            'read_at' => null, // 未読
        ]);

        // Act
        $response = $this->actingAs($user)->get('/notifications');

        // Assert: 通知一覧が表示される_未読分
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('読書計画の期日が迫っています');
        $response->assertSee('既読にする');
    }

    /** @test */
    public function 認証済みユーザーで自身の通知一覧が正常に表示される（既読）()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 通知テーブルデータ作成
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\PlanReminderNotification',
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書計画の期日が迫っています',
                'body' => '書籍「テスト書籍」の読書計画の期日が3日後に迫っています。',
                'timing' => 'three_days_before',
            ],
            'read_at' => now(), // 既読
        ]);

        // Act
        $response = $this->actingAs($user)->get('/notifications');

        // Assert: 通知一覧が表示される_既読分
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertDontSee('既読にする');
    }

    /** @test */
    public function 認証済みユーザーで自身の通知がゼロ件の場合()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/notifications');

        // Assert: 通知一覧が表示される_既読分
        $response->assertStatus(200);
        $response->assertSee('通知はありません。');
    }

    /** @test */
    public function 未認証ユーザーからのリクエストはログイン画面にリダイレクトされる()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 通知テーブルデータ作成
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\PlanReminderNotification',
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書計画の期日が迫っています',
                'body' => '書籍「テスト書籍」の読書計画の期日が3日後に迫っています。',
                'timing' => 'three_days_before',
            ],
            'read_at' => null, // 未読
        ]);

        // Act
        $response = $this->get('/notifications');

        // Assert
        // ログイン画面へリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証済みユーザーで既読するリンククリック時正常にテーブル更新される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // 通知テーブルデータ作成
        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\PlanReminderNotification',
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書計画の期日が迫っています',
                'body' => '書籍「テスト書籍」の読書計画の期日が3日後に迫っています。',
                'timing' => 'three_days_before',
            ],
            'read_at' => null, // 未読
        ]);

        // Act
        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect('/notifications'); // リダイレクト先の確認
        // セッションに成功メッセージが含まれていることを確認
        $response->assertSessionHas('success', '通知を既読に更新しました。');
        // 通知テーブルの既読日時が更新されていることを確認
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function 他人の通知に既読するリンククリック時は404エラー()
    {
        // Arrange
        // ユーザーを作成
        $myuser = User::factory()->create();  // 自分
        $otheruser = User::factory()->create();  // 他人
        // 通知テーブルデータ作成(他人)
        $notification = $otheruser->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\PlanReminderNotification',
            'notifiable_type' => get_class($otheruser),
            'notifiable_id' => $otheruser->id,
            'data' => [
                'title' => '読書計画の期日が迫っています',
                'body' => '書籍「テスト書籍」の読書計画の期日が3日後に迫っています。',
                'timing' => 'three_days_before',
            ],
            'read_at' => null, // 未読
        ]);

        // Act
        $response = $this->actingAs($myuser)->post("/notifications/{$notification->id}/read");

        // Assert
        $response->assertStatus(404);
    }
}
