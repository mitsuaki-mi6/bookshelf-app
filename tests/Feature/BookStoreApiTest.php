<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function バリデーション通過し正常な値で登録できること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(201);

        // データベースに書籍が登録されていることを確認
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
        ]);

        // ジャンルとの紐付けを確認
        $book = Book::where('title', '新規書籍')->first();
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /** @test */
    public function バリデーション：必須項目の未入力を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'description' => '',
            'image_url' => '',
            'genres' => [],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 各フィールドが未入力エラーで、指定したメッセージが含まれることを検証
        $response->assertInvalid([
            'title' => '書籍名を入力してください',
            'author' => '著者を入力してください',
            'genres' => 'ジャンルは１つ以上選択してください',
        ]);
    }

    /** @test */
    public function バリデーション：文字数上限を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 256文字以上の有効なURLを作成
        $longUrl = 'https://example.com/' . str_repeat('a', 240);

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => str_repeat('あ', 256),  // 255文字オーバー
            'author' => str_repeat('あ', 256),  // 255文字オーバー
            'isbn' => '12345678901234',  // 13桁オーバー
            'published_date' => '200999-05-07',  // 10桁オーバー
            'description' => str_repeat('あ', 256),  // 255文字オーバー
            'image_url' => $longUrl,  // キーを指定して255文字オーバーのURL
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 文字数オーバーを検証
        $response->assertInvalid([
            'title' => '書籍名は255文字以内で入力してください',
            'author' => '著者名は255文字以内で入力してください',
            'isbn' => 'ISBNコードは数字13文字で入力してください',
            'description' => '説明は255文字以内で入力してください',
            'published_date' => '出版日は有効な日付形式で入力してください',
            'image_url' => '画像URLは255文字以内で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：ISBNが13桁未満数字を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 文字数を検証
        $response->assertInvalid([
            'isbn' => 'ISBNコードは数字13文字で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：ISBNが数字以外を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890aaa',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // ISBNを検証
        $response->assertInvalid([
            'isbn' => 'ISBNコードは数字13文字で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：ISBNの重複を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // ISBNが重複する書籍を事前に作成
        Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567890123', // 重複させたいISBN
        ]);

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // ISBNを検証
        $response->assertInvalid([
            'isbn' => 'そのISBNコードは既に使用されています',
        ]);
    }

    /** @test */
    public function バリデーション：出版日が日付形式以外を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => 'aaaa-bb-cc',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 日付形式を検証
        $response->assertInvalid([
            'published_date' => '出版日は有効な日付形式で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：ジャンルの未指定を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // ジャンルの未指定を検証
        $response->assertInvalid([
            'genres' => 'ジャンルは１つ以上選択してください',
        ]);
    }

    /** @test */
    public function 存在しないジャンルを指定した場合拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [9999],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 存在しないジャンルの指定を検証
        $response->assertInvalid([
            'genres.0' => '選択されたジャンルは存在しません',
        ]);
    }

    /** @test */
    public function 画像_urlのurl形式以外を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 画像URLの形式を検証
        $response->assertInvalid([
            'image_url' => '画像URLは有効なURL形式で入力してください',
        ]);
    }

    /** @test */
    public function 登録者_idが未設定の場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 登録者IDが未設定を検証
        $response->assertInvalid([
            'user_id' => '登録者IDを入力してください',
        ]);
    }

    /** @test */
    public function 登録者_idが数値以外の場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => 'a',
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 登録者IDが数値以外を検証
        $response->assertInvalid([
            'user_id' => '登録者IDは数値で入力してください',
        ]);
    }

    /** @test */
    public function 登録者_idが存在しない場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/books', [
            'user_id' => '9999',
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);
        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 録者IDが存在しない場合を検証
        $response->assertInvalid([
            'user_id' => '指定された登録者IDは存在しません',
        ]);
    }

    /** @test */
    public function 未認証ユーザーで書籍登録しようとした場合401()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->post('/api/v1/books', [
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証されていません。',
        ]);
    }
}
