<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 正常な書籍ID、期日が今日以降の場合正常に動作すること()
    {
        // Arrange
        // 書籍と読書計画のセット
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['book_id' => $book->id]);
        // バリデーションルールの取得
        $rules = (new UpdateReadingPlanRequest)->rules();

        // データセット(全ての項目が正常に入力されている)
        $data = [
            'target_date' => now()->toDateString(),
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 必須項目の未入力を拒否する()
    {
        // Arrange
        // 書籍と読書計画のセット
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['book_id' => $book->id]);
        // バリデーションルールの取得
        $rules = (new UpdateReadingPlanRequest)->rules();

        // データセット(必須項目未入力)
        $data = [
            'target_date' => '',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }

    /** @test */
    public function 目標日が日付形式以外の場合を拒否する()
    {
        // Arrange
        // 書籍と読書計画のセット
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['book_id' => $book->id]);
        // バリデーションルールの取得
        $rules = (new UpdateReadingPlanRequest)->rules();

        // データセット(目標日が日付形式以外)
        $data = [
            'target_date' => 'invalid_date',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }

    /** @test */
    public function 目標日に今日より前の日付が指定された場合を拒否する()
    {
        // Arrange
        // 書籍と読書計画のセット
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['book_id' => $book->id]);
        // バリデーションルールの取得
        $rules = (new UpdateReadingPlanRequest)->rules();

        // データセット(目標日が今日より前の日付)
        $data = [
            'target_date' => now()->subDay()->toDateString(),
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }
}
