<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    // リレーション: レビューを投稿したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: レビュー対象の書籍
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // リレーション: レビューに対するいいね
    public function reviewLikes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    // リレーション: このレビューに「いいね」したユーザー一覧
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'review_likes', 'review_id', 'user_id');
    }
}
