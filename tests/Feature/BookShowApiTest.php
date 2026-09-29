<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class BookShowApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍詳細にアクセスし正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create(['name' => 'テスト太郎']);

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍データを作成
        $book = Book::factory()->create([
            'title' => '書籍001',
            'author' => '新規著者001',
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre1->id);
        $book->genres()->attach($genre2->id);

        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson("/api/v1/books/{$book->id}");

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // 情報確認
            ->assertJsonFragment(['title' => '書籍001']) // 書籍
            ->assertJsonFragment(['name' => '小説']) // ジャンル1
            ->assertJsonFragment(['name' => 'マンガ']) // ジャンル2
            ->assertJsonFragment([ // レビュー
                'user_name' => 'テスト太郎',
                'rating' => 5,
                'comment' => 'テストコメント',
            ]);
    }

    /** @test */
    public function 存在しない書籍ID指定された場合（404）(): void
    {
        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson("/api/v1/books/999");

        // Assert
        // バリデーション通過しステータスコードが404であることを確認
        $response->assertStatus(404);

        // レスポンス情報を確認
        $response->assertJsonFragment(['message' => '指定された書籍が見つかりませんでした。']);
    }

    /** @test */
    public function レビュー情報（件数、評価平均）が正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍データを作成
        $book = Book::factory()->create([
            'title' => '書籍001',
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
        $response = $this->getJson("/api/v1/books/{$book->id}");

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
    public function レビューゼロの場合、書籍詳細にアクセスし正しく返される(): void
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍データを作成
        $book = Book::factory()->create([
            'title' => '書籍001',
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act
        // 書籍一覧APIにGETリクエストを送信
        $response = $this->getJson("/api/v1/books/{$book->id}");

        // Assert
        // バリデーション通過しステータスコードが200であることを確認
        $response->assertStatus(200);

        // レスポンス情報を確認
        $response
            // レビュー情報を確認
            ->assertJsonFragment(['title' => '書籍001'])
            ->assertJsonFragment(['reviews_count' => 0]);
    }
}
