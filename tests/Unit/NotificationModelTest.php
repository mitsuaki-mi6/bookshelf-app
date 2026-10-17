<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １人のユーザに対して複数の通知が取得できること()
    {
        // Arrange
        $user = User::factory()->create();

        // ユーザー通知を3件作成（Laravelが自動でDBに保存）
        for ($i = 1; $i <= 3; $i++) {
            $user->notify(new class extends BaseNotification {
                public function via($notifiable)
                {
                    return ['database'];
                }

                public function toArray($notifiable)
                {
                    return ['message' => 'テスト通知'];
                }
            });
        }

        // Act
        // ユーザーから通知情報を取得する
        $results = $user->notifications;

        // Assert
        // 3件取得を確認する
        $this->assertCount(3, $results);
    }
}
