<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧にアクセスし正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍001',
            'author' => '新規著者001',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍002',
            'author' => '新規著者002',
            'isbn' => '1234567890999',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book1->genres()->attach($genre1->id);
        $book1->genres()->attach($genre2->id);
        $book2->genres()->attach($genre1->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // 書籍が複数件
            ->assertJsonFragment(['title' => '書籍001'])
            ->assertJsonFragment(['title' => '書籍002'])
            // ジャンルが複数件
            ->assertJsonFragment(['name' => '小説'])
            ->assertJsonFragment(['name' => 'マンガ']);
    }

    /** @test */
    public function １ページあたりの件数指定時書籍一覧が正しく返される_母数が11件(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books?per_page=3');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // ページネーションを確認
            ->assertJsonFragment(['per_page' => 3])
            ->assertJsonFragment(['to' => 3])
            ->assertJsonFragment(['total' => 11]);
    }

    /** @test */
    public function レビュー情報が正しく返される(): void
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

        // レビューを作成(評価平均3の想定)
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => '5',
            'comment' => 'テストコメント',
        ]);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => '1',
        ]);

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books?per_page=3');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // レビュー情報を確認
            ->assertJsonFragment(['average_rating' => 3])
            ->assertJsonFragment(['reviews_count' => 2])
            ->assertJsonFragment(['comment' => 'テストコメント']);
    }

    /** @test */
    public function 表示件数がゼロ件（書籍情報なし）(): void
    {
        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);
        // レスポンス情報を確認
        $response->assertJsonFragment(['message' => '該当する書籍が見つかりませんでした。']);
    }

    /** @test */
    public function ２ページ目の書籍一覧が正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books?per_page=3&page=2');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // 2ページ目のレスポンスを確認
            ->assertJsonFragment(['current_page' => 2])
            ->assertJsonFragment(['from' => 4])
            ->assertJsonFragment(['to' => 6]);
    }

    /** @test */
    public function 表示件数が不正な値の時エラー（422）(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 書籍一覧APIにGETリクエストを送信(不正な表示件数)
        $response = $this->getJson('/api/v1/books?per_page=a');

        // Assert
        // 422とエラーメッセージ確認
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => '入力内容に誤りがあります。']);
    }

    /** @test */
    public function ページ番号が不正な値の時エラー（422）(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 書籍一覧APIにGETリクエストを送信(不正なページ番号)
        $response = $this->getJson('/api/v1/books?per_page=3&page=a');

        // Assert
        // 422とエラーメッセージ確認
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => '入力内容に誤りがあります。']);
    }

    /** @test */
    public function キーワード検索で該当の書籍一覧が正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍検索',
            'author' => '新規著者001',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍002',
            'author' => '著者検索',
            'isbn' => '1234567890999',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book1->genres()->attach($genre1->id);
        $book1->genres()->attach($genre2->id);
        $book2->genres()->attach($genre1->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson('/api/v1/books?keyword=検索');

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // keyword「検索」がヒット
            ->assertJsonFragment(['title' => '書籍検索'])
            ->assertJsonFragment(['author' => '著者検索']);
    }

    /** @test */
    public function キーワード検索で該当ゼロが返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍001',
            'author' => 'テスト著者001',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍002',
            'author' => 'テスト著者002',
            'isbn' => '1234567890999',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book1->genres()->attach($genre1->id);
        $book1->genres()->attach($genre2->id);
        $book2->genres()->attach($genre1->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信(検索ヒットゼロ)
        $response = $this->getJson('/api/v1/books?keyword=検索');

        // Assert
        // 検索ヒットなしの確認200とメッセージ
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => '該当する書籍が見つかりませんでした。']);
    }

    /** @test */
    public function ジャンル絞り込みで該当の書籍一覧が返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍001',
            'author' => 'テスト著者001',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍002',
            'author' => 'テスト著者002',
            'isbn' => '1234567890999',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book1->genres()->attach($genre1->id);
        $book1->genres()->attach($genre2->id);
        $book2->genres()->attach($genre1->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信(ジャンルIDで絞込)
        $response = $this->getJson('/api/v1/books?genre_id=1');

        // Assert
        // ステータスコードが200であることを確認
        $response->assertStatus(200);
        // レスポンス情報を確認
        $response
            // ジャンルIDで絞込がヒット
            ->assertJsonFragment(['title' => 'テスト書籍001'])
            ->assertJsonFragment(['title' => 'テスト書籍002']);
    }

    /** @test */
    public function キーワード検索とジャンル絞り込みで該当の書籍一覧が返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍001',
            'author' => 'テスト検索著者001',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍002',
            'author' => 'テスト著者002',
            'isbn' => '1234567890999',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 書籍とジャンルを紐付け
        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信(ジャンルIDで絞込)
        $response = $this->getJson('/api/v1/books?genre_id=1&keyword=検索');

        // Assert
        // ステータスコードが200であることを確認
        $response->assertStatus(200);
        // レスポンス情報を確認
        $response
            // 条件に合う book1 が含まれていること
            ->assertJsonFragment(['title' => 'テスト書籍001'])
            // 条件に合わない book2 が含まれていないこと
            ->assertJsonMissing(['title' => 'テスト書籍002']);
    }

    /** @test */
    public function バリデーション：存在しないジャンルが指定された場合エラー（422）(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 書籍一覧APIにGETリクエストを送信(存在しないジャンルIDを指定)
        $response = $this->getJson('/api/v1/books?genre_id=999');

        // Assert
        // 422とエラーメッセージ確認
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => '入力内容に誤りがあります。']);
    }

    /** @test */
    public function バリデーション：キーワード文字数超過時（422）(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成しユーザーIDを紐付けつ
        $books = Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        // 作成したすべての書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act
        // 256文字のキーワードを生成してGETリクエストを送信
        $keyword = str_repeat('あ', 256);
        $response = $this->getJson("/api/v1/books?keyword={$keyword}");

        // Assert
        // 422とエラーメッセージ確認
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => '入力内容に誤りがあります。']);
    }
}
