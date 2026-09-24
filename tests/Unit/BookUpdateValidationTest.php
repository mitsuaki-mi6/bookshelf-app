<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Genre;
use App\Models\Book;
use App\Models\User;


class BookUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    // 各テストメソッドが実行される共通処理
    protected function setUp(): void
    {
        parent::setUp();

        // ここに共通の事前準備を書く
        Genre::factory()->create(['id' => 1, 'name' => '小説']);
        Genre::factory()->create(['id' => 2, 'name' => '漫画']);
    }

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = $this->getRules($book);

        // データセット(正常系 全ての項目が入力されている)
        $data = [
            'user_id' => $user->id,
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーション通過を確認
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function ISBNのが自分と同じ値は正常に動作する()
    {
        // Arrange
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '既存のタイトル',
            'author' => '既存の著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
        ]);

        // バリデーションルールの取得
        $rules = $this->getRules($book);

        // データセット((自分と同じISBN)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123', // 同じISBN
            'published_date' => '2023-12-01',
            'genres' => [1],
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
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        // バリデーションルールの取得
        $rules = $this->getRules($book);

        // データセット(異常系 必須項目が未入力)
        $data = [
            'user_id' => $user->id,
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'genres' => [],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('author', $validator->errors()->toArray());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
        $this->assertArrayHasKey('published_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('genres', $validator->errors()->toArray());
    }

    /** @test */
    public function タイトルの文字数上限オーバーを拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(制限文字オーバー：256文字の文字列を作成)
        $data = [
            'title' => str_repeat('あ', 256), // 255文字を超える文字列
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function 著者名の文字数上限（256文字）を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(制限文字オーバー：256文字の文字列を作成)
        $data = [
            'title' => 'テストタイトル',
            'author' => str_repeat('あ', 256), // 255文字を超える文字列
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('author', $validator->errors()->toArray());
    }

    /** @test */
    public function ISBNが数字以外を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ISBNが数字以外)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => 'aaa4567890123',
            'published_date' => '2023-01-01',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
    }

    /** @test */
    public function ISBNが13桁未満数字を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ISBNが13桁未満)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '123456789',
            'published_date' => '2023-01-01',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
    }
    /** @test */
    public function ISBNが14桁数字を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ISBNが14桁数字)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '12345678901234',
            'published_date' => '2023-01-01',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
    }
    /** @test */
    public function ISBNの重複を拒否する_自分以外()
    {
        // Arrange
        $user = User::factory()->create();
        Book::create([
            'user_id' => $user->id,
            'title' => '既存のタイトル',
            'author' => '既存の著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
        ]);

        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ISBNが重複)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123', // 重複するISBN
            'published_date' => '2023-12-01',
            'genres' => [1],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
    }

    /** @test */
    public function 出版日が日付形式以外を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(出版日が日付形式以外)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '9999-15-40',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('published_date', $validator->errors()->toArray());
    }
    /** @test */
    public function ジャンルの未指定を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ジャンルが未指定)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-12-01',
            'genres' => [],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('genres', $validator->errors()->toArray());
    }
    /** @test */
    public function ジャンルがDBに存在しない場合拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(ジャンルが存在しない)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-12-01',
            'genres' => [9],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        // `genres.0` または `genres` のどちらかにエラーが含まれていることを確認する
        $errors = $validator->errors()->keys();
        $this->assertTrue(in_array('genres', $errors) || in_array('genres.0', $errors));
    }
    /** @test */
    public function 画像URLのURL形式以外を拒否する()
    {
        // Arrange
        // バリデーションルールの取得
        $rules = $this->getRules();

        // データセット(画像URLがURL形式以外)
        $data = [
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-12-01',
            'image_url' => 'example.com/image.jpg',
            'genres' => [1, 2],
        ];

        // Act バリデーションの実行
        $validator = \Validator::make($data, $rules);

        // Assert バリデーションが失敗することを確認
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image_url', $validator->errors()->toArray());
    }

    /**
     * バリデーションルールを取得するヘルパーメソッド
     */
    protected function getRules($book = null)
    {
        $request = new \App\Http\Requests\UpdateBookRequest();

        if (!$book) {
            $book = \App\Models\Book::factory()->create();
        }

        // ルーターに登録せず、ルートインスタンスに直接パラメータをセットする（一番安全で確実な方法）
        $route = new \Illuminate\Routing\Route(['PUT'], 'books/{book}', []);
        $route->parameters['book'] = $book;

        // リクエストにルートリゾルバを設定
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return $request->rules();
    }
}
