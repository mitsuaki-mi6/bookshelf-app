<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // テーブル名を明示的に指定（必要に応じて）
    protected $table = 'notifications';

    public $incrementing = false;     // 自動インクリメントを無効化
    protected $keyType = 'string';    // 主キーの型を文字列（UUID）に指定

    // 一括代入を許可するカラム
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    // dataカラムを配列やJSONとして扱いたい場合のキャスト設定
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
