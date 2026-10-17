<?php

namespace Tests\Unit;

use App\Http\Requests\IndexReadingPlanRequest;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanIndexValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ステータスが正しい値の場合、正常に動作すること()
    {
        // Arrange
        // 読書計画のセット
        $readingPlan = ReadingPlan::factory()->create();
        // バリデーションルールの取得
        $rules = (new IndexReadingPlanRequest)->rules();

        // データセット(全ての項目が正常に入力されている)
        $data = [
            'status' => 'completed',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function ステータスが未指定（省略された場合）でも正常に動作すること()
    {
        // Arrange
        // 読書計画のセット
        $readingPlan = ReadingPlan::factory()->create();
        // バリデーションルールの取得
        $rules = (new IndexReadingPlanRequest)->rules();

        // データセット(未入力_デフォルト値が適用される)
        $data = [
            'status' => null,
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function ステータスが不正な値の場合を拒否する()
    {
        // Arrange
        // 読書計画のセット
        $readingPlan = ReadingPlan::factory()->create();
        // バリデーションルールの取得
        $rules = (new IndexReadingPlanRequest)->rules();

        // データセット(不正な値)
        $data = [
            'status' => 'invalid_status',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }
}
