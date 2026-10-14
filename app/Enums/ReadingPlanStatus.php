<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => '進行中',
            self::COMPLETED => '完了',
            self::EXPIRED => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::EXPIRED => 'bg-red-100 text-red-800',
        };
    }
}
