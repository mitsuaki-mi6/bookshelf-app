<?php

namespace App\Console\Commands;

use App\Models\ReadingPlan;
use App\Notifications\PlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReadingPlanNotifications extends Command
{
    // 1. ターミナルから実行するときのコマンド名
    protected $signature = 'app:send-reading-plan-notifications';

    // 2. コマンドの説明
    protected $description = '読書計画のステータス更新と通知テーブル登録';

    // 3. コマンドの実行処理
    public function handle()
    {
        $today = Carbon::today();

        // ----------------------------------------------------------------
        // 読書計画テーブルの更新（期日を過ぎたものを expired に変更）
        // ----------------------------------------------------------------
        ReadingPlan::where('status', 'in_progress')
            ->where('target_date', '<', $today)
            ->update(['status' => 'expired']);

        // ----------------------------------------------------------------
        // 通知テーブルの作成
        // ----------------------------------------------------------------
        // 期日3日前の計画を抽出
        $targetDate3DaysBefore = $today->copy()->addDays(3);
        $plans3DaysBefore = ReadingPlan::where('status', 'in_progress')
            ->whereDate('target_date', $targetDate3DaysBefore)
            ->get();
        // 期日3日前の計画のレコード作成
        foreach ($plans3DaysBefore as $plan) {
            $plan->user->notify(new PlanReminderNotification($plan, 'three_days_before'));
        }

        // 期日当日の計画を抽出
        $targetDateToday = $today;
        $plansToday = ReadingPlan::where('status', 'in_progress')
            ->whereDate('target_date', $targetDateToday)
            ->get();
        // 期日当日の計画のレコード作成
        foreach ($plansToday as $plan) {
            $plan->user->notify(new PlanReminderNotification($plan, 'today'));
        }

        // 期日3日後の計画を抽出
        $targetDate3Daysafter = $today->copy()->subDays(3);
        $plans3Daysafter = ReadingPlan::where('status', 'expired')
            ->whereDate('target_date', $targetDate3Daysafter)
            ->get();
        // 期日3日後の計画のレコード作成
        foreach ($plans3Daysafter as $plan) {
            $plan->user->notify(new PlanReminderNotification($plan, 'three_days_after'));
        }

        $this->info('読書計画のバッチ処理が完了しました。');
    }
}
