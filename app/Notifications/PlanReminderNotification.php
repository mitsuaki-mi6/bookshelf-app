<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlanReminderNotification extends Notification
{
    use Queueable;

    protected $plan;

    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($plan, string $type)
    {
        $this->plan = $plan;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // データベースに保存
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // ブレードが想定しているタイミング名（on_due_date など）に合わせる
        $timing = match ($this->type) {
            'three_days_before' => 'three_days_before',
            'today' => 'on_due_date',
            'three_days_after' => 'three_days_after',
            default => 'default',
        };

        // タイトルと本文を定義
        $title = match ($this->type) {
            'three_days_before' => '読書計画の期日が迫っています',
            'today' => '読書計画の期日です',
            'three_days_after' => '読書計画の期限が過ぎました',
            default => '読書計画のリマインダー',
        };

        $body = match ($this->type) {
            'three_days_before' => "書籍「{$this->plan->book->title}」の読書計画の期日が3日後に迫っています。",
            'today' => "書籍「{$this->plan->book->title}」の読書計画の期日は本日です。",
            'three_days_after' => "書籍「{$this->plan->book->title}」の読書計画の期日から3日が経過しました（期限切れ）。",
            default => '読書計画を確認してください。',
        };

        return [
            'reading_plan_id' => $this->plan->id,
            'timing' => $timing,
            'title' => $title,
            'body' => $body,
        ];
    }
}
