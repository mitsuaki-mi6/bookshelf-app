<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * シリアライズ時に隠す属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャストする必要のある属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * リレーション: ユーザーが投稿した書籍
     *
     * @return HasMany：書籍モデルのコレクションへのリレーション
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * リレーション: ユーザーが投稿したレビュー
     *
     * @return HasMany：レビューモデルのコレクションへのリレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * リレーション: ユーザーがいいねしたレビュー
     *
     * @return BelongsToMany：レビューモデルのコレクションへのリレーション
     */
    public function likedReviews(): BelongsToMany
    {
        return $this->belongsToMany(Review::class, 'review_likes', 'user_id', 'review_id')
            ->withTimestamps();
    }

    /**
     * リレーション: ユーザーがお気に入り登録した書籍
     *
     * @return BelongsToMany：書籍モデルのコレクションへのリレーション
     */
    public function favoriteBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'favorites', 'user_id', 'book_id')
            ->withTimestamps();
    }
}
