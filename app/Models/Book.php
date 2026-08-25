<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Genre;

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

    // リレーション: 書籍を登録したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: 書籍に対するレビュー
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // リレーション: 書籍のお気に入り登録
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // リレーション: 書籍のジャンル（多対多）
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre')
            ->withTimestamps();
    }
}
