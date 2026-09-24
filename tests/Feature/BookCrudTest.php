<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\ReviewLike;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍登録処理でジャンルと紐付いた書籍が登録される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/books', [
            'title' => '新規書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);

        // Assert
        $response->assertStatus(302);

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
    public function 書籍更新処理で書籍データが変更される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '新規ジャンル']);
        $genre2 = Genre::factory()->create(['name' => '更新ジャンル']);

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => '新規著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // Act
        $response = $this->actingAs($user)->put("/books/{$book->id}", [
            'title' => '更新書籍',
            'author' => '更新著者',
            'isbn' => '0123456789012',
            'published_date' => '2000-05-07',
            'description' => '更新説明',
            'image_url' => 'https://example.com/image002.jpg',
            'genres' => [$genre2->id],
        ]);

        // Assert
        $response->assertStatus(302);

        // データベースに書籍が登録されていることを確認
        $this->assertDatabaseHas('books', [
            'title' => '更新書籍',
            'author' => '更新著者',
            'isbn' => '0123456789012',
            'published_date' => '2000-05-07',
            'description' => '更新説明',
            'image_url' => 'https://example.com/image002.jpg',
        ]);

        // ジャンルとの紐付けを確認
        $book = Book::where('title', '更新書籍')->first();
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);
    }

    /** @test */
    public function 書籍更新処理で他人の場合は403エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '新規ジャンル']);
        $genre2 = Genre::factory()->create(['name' => '更新ジャンル']);

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => '新規著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // Act
        $response = $this->actingAs($otherUser)->put("/books/{$book->id}", [
            'title' => '更新書籍',
            'author' => '更新著者',
            'isbn' => '0123456789012',
            'published_date' => '2000-05-07',
            'description' => '更新説明',
            'image_url' => 'https://example.com/image002.jpg',
            'genres' => [$genre2->id],
        ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function 書籍更新処理で書籍が存在しない場合は404エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '新規ジャンル']);
        $genre2 = Genre::factory()->create(['name' => '更新ジャンル']);

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '新規書籍',
            'author' => '新規著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '新規説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // Act
        $response = $this->actingAs($user)->put("/books/9999", [
            'description' => '更新説明',
            'genres' => [$genre2->id],
        ]);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function 書籍削除処理で書籍（books）は削除日時が更新され関連テーブルは削除される()
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

        // レビューを作成
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
        ]);

        // いいねを作成
        ReviewLike::factory()->create([
            'review_id' => $review->id,
            'user_id' => $user->id, // ログイン中のユーザーがいいね（ON状態のテスト）
        ]);

        // お気に入りを作成
        $favorite = Favorite::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
        ]);

        // Act
        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        // Assert
        $response->assertStatus(302);

        // 書籍の論理削除（deleted_atに日時が入る）を確認
        $this->assertNotNull($book->fresh()->deleted_at);

        // 中間テーブル（book_genre）からデータが削除されていることを確認
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // お気に入りが削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);

        // レビューが削除されていることを確認
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        // レビューのいいねが削除されていることを確認
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 書籍削除処理で他人の場合は403エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act
        $response = $this->actingAs($otherUser)->delete("/books/{$book->id}");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function 書籍削除処理で書籍が存在しない場合は404エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->delete("/books/999");

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function 書籍詳細（レビュー投稿処理）でレビューが登録される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 書籍とジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act
        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => 'テスト評価コメント',
        ]);

        // Assert
        $response->assertStatus(302);

        // レビューが登録されていることを確認
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'テスト評価コメント',
        ]);
    }

    /** @test */
    public function 書籍詳細処理で書籍が存在しない場合は404エラー()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("/books/999");

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function 未認証ユーザーで書籍登録しようとした場合ログイン画面にリダイレクト()
    {
        // Act: 未認証ユーザーで書籍登録にアクセス
        $response = $this->post("/books");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーで書籍更新しようとした場合ログイン画面にリダイレクト()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();

        // Act: 未認証ユーザーで書籍更新にアクセス
        $response = $this->put("/books/{$book->id}");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーで書籍削除しようとした場合ログイン画面にリダイレクト()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();

        // Act: 未認証ユーザーで書籍削除にアクセス
        $response = $this->delete("/books/{$book->id}");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーでレビュー投稿しようとした場合ログイン画面にリダイレクト()
    {
        // Arrange
        // 書籍を作成
        $book = Book::factory()->create();

        // Act: 未認証ユーザーでレビュー投稿にアクセス
        $response = $this->post("/books/{$book->id}/reviews");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
