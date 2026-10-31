<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
    ];

    /**
     * リレーション: いいねしたユーザー
     *
     * @return BelongsTo：ユーザーモデルへのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: いいねされたレビュー
     *
     * @return BelongsTo：レビューモデルへのリレーション
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
