<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'status' => ReadingPlanStatus::class,
    ];

    /**
     * リレーション: 読書計画を登録したユーザー
     *
     * @return BelongsTo：ユーザーモデルへのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: 読書計画に登録された書籍
     *
     * @return BelongsTo：書籍モデルへのリレーション
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
