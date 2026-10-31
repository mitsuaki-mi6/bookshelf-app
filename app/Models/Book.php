<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    // 追加：型設定  2026/10/10 MN
    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * リレーション: 書籍を登録したユーザー
     *
     * @return BelongsTo：ユーザーモデルへのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: 書籍に対するレビュー
     *
     * @return HasMany：レビューモデルのコレクションへのリレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * リレーション: 書籍のお気に入り登録
     *
     * @return HasMany：お気に入りモデルのコレクションへのリレーション
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * リレーション: 書籍のジャンル（多対多）
     *
     * @return BelongsToMany：ジャンルモデルのコレクションへのリレーション
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre')
            ->withTimestamps();
    }

    /**
     * リレーション: 書籍の読書計画
     *
     * @return HasMany：読書計画モデルのコレクションへのリレーション
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
