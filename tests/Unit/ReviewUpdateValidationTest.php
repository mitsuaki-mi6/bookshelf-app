<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\User;
use App\Models\Review;

class ReviewUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること()
    {
        // Arrange
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '既存の評価コメント',
        ]);

        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(正常系 全ての項目が入力されている)
        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
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
        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '既存の評価コメント',
        ]);

        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(異常系 一部の項目が入力されていない)
        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '',
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    /** @test */
    public function 評価（rating）が1～5以外を拒否する()
    {
        // Arrange
        // ユーザ・書籍情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '既存の評価コメント',
        ]);

        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(異常系 一部の項目が入力されていない)
        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 0,
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
        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '既存の評価コメント',
        ]);

        // バリデーションルールの取得
        $rules = (new ReviewRequest())->rules();

        // データセット(異常系 一部の項目が入力されていない)
        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 0,
            'comment' => str_repeat('a', 1001), // 1001文字のコメント
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }
}
