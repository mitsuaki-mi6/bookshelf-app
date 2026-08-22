<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
    ];

    // リレーション: いいねしたユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: いいねされたレビュー
    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
