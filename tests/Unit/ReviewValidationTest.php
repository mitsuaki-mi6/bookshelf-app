<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\User;

class ReviewValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること()
    {
        // Arrange
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(正常系 全ての項目が入力されている)
        $data = [
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => 'テスト評価コメント',
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
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(異常系 必須項目が未入力)
        $data = [
            'user_id' => $user->id,
            'rating' => '',
            'comment' => '',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rating', $validator->errors()->toArray());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    /** @test */
    public function 評価（rating）が1～5以外を拒否する()
    {
        // Arrange
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(評価が1～5以外)
        $data = [
            'user_id' => $user->id,
            'rating' => 6,
            'comment' => 'テスト評価コメント',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rating', $validator->errors()->toArray());
    }

    /** @test */
    public function コメントの文字数上限（1000文字）を拒否する()
    {
        // Arrange
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(コメントの文字数上限オーバー)
        $data = [
            'user_id' => $user->id,
            'rating' => 6,
            'comment' =>  str_repeat('あ', 1001), // 1000文字を超える文字列,
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }
}
