<?php

namespace Tests\Unit;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new GenreRequest)->rules();

        // データセット(正常系 全ての項目が入力されている)
        $data = [
            'name' => 'テストジャンル',
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
        // バリデーションルールの取得
        $rules = (new GenreRequest)->rules();

        // データセット(必須項目が未入力)
        $data = [
            'name' => '',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function ジャンル名の文字数上限（255文字）を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new GenreRequest)->rules();

        // データセット(ジャンル名の文字数上限オーバー)
        $data = [
            'name' => str_repeat('あ', 256), // 255文字を超える文字列,
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function 同じジャンル名が存在する場合を拒否する()
    {
        // Arrange
        Genre::create([
            'name' => '既存のジャンル',
        ]);

        // バリデーションルールの取得
        $rules = (new GenreRequest)->rules();

        // データセット(ジャンル名が重複)
        $data = [
            'name' => '既存のジャンル',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
