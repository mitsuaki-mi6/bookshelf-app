<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function バリデーション通過し正常な値で更新できること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '既存書籍',
            'author' => '既著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '既説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre1->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890456',
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre2->id],
        ]);

        // Assert
        $response->assertStatus(200);

        // 書籍テーブルが更新されていることを確認
        $this->assertDatabaseHas('books', [
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890456',
            'published_date' => '2020-05-07 00:00:00',  // 20261024 修正  MN
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
        ]);

        // ジャンルとの紐付け変更確認
        $book = Book::where('title', '変更書籍')->first();
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);
    }

    /** @test */
    public function isb_nが自身でも重複エラーとならず更新できること()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '既存書籍',
            'author' => '既著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '既説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(200);

        // ISBNが同じでもエラーにならず書籍テーブルが更新されていることを確認
        $this->assertDatabaseHas('books', [
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '2020-05-07 00:00:00',  // 20261024 修正  MN
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
        ]);
    }

    /** @test */
    public function 存在しない書籍_i_d指定された場合（404）()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '既存書籍',
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ（存在しないID '9999' を指定）
        $response = $this->actingAs($user)->put('/api/v1/books/9999', [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'message' => '指定された書籍が見つかりませんでした。',
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

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ（必須データなし）
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
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
            'isbn' => 'ISBNコードを入力してください',
            'published_date' => '出版日を入力してください',
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

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // 256文字以上の有効なURLを作成
        $longUrl = 'https://example.com/' . str_repeat('a', 240);

        // Act  編集データ（文字数オーパ）
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
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
    public function バリデーション：_isb_nが13桁未満数字を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ（ISBN13桁未満）
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '12345678',  // 13桁未満
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
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
    public function バリデーション：_isb_nが数字以外を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890aaa',  // 数字以外
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // ISBNが数字以外を検証
        $response->assertInvalid([
            'isbn' => 'ISBNコードは数字13文字で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：_isb_nの重複を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // ISBNが重複する書籍を事前に作成
        Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567890123', // 重複させたいISBN
        ]);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',  // 重複するISBN
            'published_date' => '2020-05-07',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // ISBNの重複を検証
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

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => 'aaaa-bb-cc', // 日付形式以外
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 出版日が日付形式以外を検証
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

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [], // ジャンルの未指定
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
    public function バリデーション：存在しないジャンルを指定した場合拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
            'genres' => [9999], // 存在しないジャンルを指定
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // 存在しないジャンル指定を検証
        $response->assertInvalid([
            'genres.0' => '選択されたジャンルは存在しません',
        ]);
    }

    /** @test */
    public function バリデーション：画像_ur_lの_ur_l形式以外を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => '.com/image_change.jpg',  // URL形式以外
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(422);  // バリデーションエラー
        $response->assertJson([
            'message' => '入力内容に誤りがあります。',
        ]);
        // URL形式以外を検証
        $response->assertInvalid([
            'image_url' => '画像URLは有効なURL形式で入力してください',
        ]);
    }

    /** @test */
    public function バリデーション：登録者_i_dが未設定の場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
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
    public function バリデーション：登録者_i_dが数値以外の場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => 'a',  // 登録者IDが数値以外
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
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
    public function バリデーション：登録者_i_dが存在しない場合を拒否する()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act  編集データ
        $response = $this->actingAs($user)->put("/api/v1/books/{$book->id}", [
            'user_id' => '99999',  // 登録者IDが存在しない
            'title' => '変更書籍',
            'author' => '変更著者',
            'isbn' => '1234567890123',
            'published_date' => '1966-01-03',
            'description' => '変更説明',
            'image_url' => 'https://example.com/image_change.jpg',
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
}
