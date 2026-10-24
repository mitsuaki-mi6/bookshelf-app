<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            // 通知ID（UUID）
            'id' => (string) Str::uuid(),

            // 通知のクラス名（アプリ内で使用している通知クラス）
            'type' => 'App\Notifications\PlanReminderNotification',

            // 誰宛の通知か
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),

            // 通知のメッセージやデータ（JSONとして保存される）
            'data' => [
                'message' => '読書計画の期日が近づいています。',
                'reading_plan_id' => 1,
            ],

            // 既読日時（nullなら未読、日付が入っていれば既読）
            'read_at' => null,
        ];
    }
}
