<?php

namespace Tests\Unit;

use App\Http\Requests\StoreReadingPlanRequest;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ReadingPlanValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 正常な書籍ID、期日が今日以降の場合正常に動作すること()
    {
        // Arrange
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(全ての項目が正常に入力されている)
        $data = [
            'book_id' => $book->id,
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
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(必須項目未入力)
        $data = [
            'book_id' => '',
            'target_date' => '',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('book_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }

    /** @test */
    public function 書籍IDが数値以外を拒否する()
    {
        // Arrange
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(書籍IDが数値以外)
        $data = [
            'book_id' => 'invalid_book_id',
            'target_date' => now()->toDateString(),
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('book_id', $validator->errors()->toArray());
    }

    /** @test */
    public function 書籍が存在しない場合を拒否する()
    {
        // Arrange
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(存在しない書籍ID)
        $data = [
            'book_id' => 999,
            'target_date' => now()->toDateString(),
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('book_id', $validator->errors()->toArray());
    }

    /** @test */
    public function 目標日が日付形式以外の場合を拒否する()
    {
        // Arrange
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(目標日が日付形式以外)
        $data = [
            'book_id' => $book->id,
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
        // 書籍のセット
        $book = Book::factory()->create();
        // バリデーションルールの取得
        $rules = (new StoreReadingPlanRequest)->rules();

        // データセット(目標日が今日より前の日付)
        $data = [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }
}
