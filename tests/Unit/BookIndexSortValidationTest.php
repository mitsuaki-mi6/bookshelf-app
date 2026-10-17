<?php

namespace Tests\Unit;

use App\Http\Requests\IndexBookRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexSortValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 指定のsort値の場合正常に動作すること()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(正常系)
        $data = [
            'sort' => 'rating',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 指定外のsort値の場合エラーとなること()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(指定外のsort値)
        $data = [
            'sort' => 'invalid_sort_value',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('sort'));
    }
}
