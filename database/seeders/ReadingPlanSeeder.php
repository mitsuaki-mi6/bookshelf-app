<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 山田太郎のデータ (5件)
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 1,
            'target_date' => Carbon::today()->addDays(3),
            'status' => '進行中',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 2,
            'target_date' => Carbon::today(),
            'status' => '進行中',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 3,
            'target_date' => Carbon::today()->subDays(3),
            'status' => '進行中',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 4,
            'target_date' => Carbon::today()->addDays(7),
            'status' => '進行中',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 5,
            'target_date' => Carbon::today()->subDays(10),
            'status' => '完了',
            'completed_at' => Carbon::today()->subDays(5),
        ]);

        // 鈴木花子のデータ (1件 - 認可テスト用)
        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => 6,
            'target_date' => Carbon::today()->addDays(5),
            'status' => '進行中',
        ]);
    }
}
