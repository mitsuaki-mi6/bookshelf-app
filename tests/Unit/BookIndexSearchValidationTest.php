<?php

namespace Tests\Unit;

use App\Http\Requests\IndexBookRequest;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexSearchValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 正常な値が入力されて検索できること()
    {
        // Arrange
        // ユーザ、ジャンルのセット
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(正常系)
        $data = [
            'keyword' => 'テストタイトル',
            'genres' => [$genre->id],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function キーワード文字数上限（255文字）を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(制限文字オーバー：256文字の文字列を作成)
        $data = [
            'keyword' => str_repeat('あ', 256), // 255文字を超える文字列
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->toArray());
    }

    /** @test */
    public function ジャンルIDが数字以外を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(ジャンルが数字以外)
        $data = [
            'genre' => ['a'],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('genre'));
    }

    /** @test */
    public function ジャンルIDが存在しない場合を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = (new IndexBookRequest)->rules();

        // データセット(ジャンルが存在しない)
        $data = [
            'genre' => [999],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('genre'));
    }
}
