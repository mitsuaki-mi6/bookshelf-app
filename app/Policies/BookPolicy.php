<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * ユーザーがモデルを更新できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  Book  $book：書籍モデル
     * @return bool：権限の有無
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * ユーザーがモデルを削除できるかどうかを判定する
     *
     * @param  User  $user：ユーザーモデル
     * @param  Book  $book：書籍モデル
     * @return bool：権限の有無
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
