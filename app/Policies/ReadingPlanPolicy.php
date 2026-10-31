<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * ユーザーがモデルを更新できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  ReadingPlan  $readingPlan：読書計画モデル
     * @return bool：権限の有無
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * ユーザーがモデルを削除できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  ReadingPlan  $readingPlan：読書計画モデル
     * @return bool：権限の有無
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
