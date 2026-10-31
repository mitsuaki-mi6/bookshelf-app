<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * ユーザーがモデルを更新できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  Review  $review：レビューモデル
     * @return bool：権限の有無
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * ユーザーがモデルを削除できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  Review  $review：レビューモデル
     * @return bool：権限の有無
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
