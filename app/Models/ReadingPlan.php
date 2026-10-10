<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    // リレーション: 読書計画を登録したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: 読書計画に登録された書籍
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
