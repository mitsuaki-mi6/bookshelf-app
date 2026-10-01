<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewEditAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証認可済ユーザーでアクセス時レビュー編集画面が正しく表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '既登録テスト書籍',
            'author' => '既登録テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '既登録テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
            'rating' => 1,
            'comment' => '既存のテストレビュー',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act: 認証済みユーザーでレビュー編集にアクセス
        $response = $this->actingAs($user)->get(route('reviews.edit', $Review));

        // Assert
        // レビュー編集情報の表示を確認
        $response->assertStatus(200);

        // 既登録項目表示確認
        $response->assertSee('既登録テスト書籍');
        $response->assertSee('既存のテストレビュー');
        $response->assertSee('★'); // 評価の星の表示確認(1つしか色がつかないため)
        $response->assertSee('キャンセル');
        $response->assertSee('更新する');
    }

    /** @test */
    public function 認証済未認可でアクセス時403エラー()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create(); // ← 他人用のユーザー
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 他人が登録した書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '他人のテスト書籍',
            'author' => '他人のテスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => '他人のテスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // レビューを作成・book_idを紐付け
        $Review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id, // 他人のユーザーID
            'rating' => 1,
            'comment' => '既存のテストレビュー',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーで他人のレビュー編集にアクセス
        $response = $this->actingAs($user)->get(route('reviews.edit', $Review));

        // Assert
        // 他人のレビューのため、403エラー（アクセス拒否）が返ることを確認
        $response->assertStatus(403);
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Arrange: レビューを作成
        $Review = Review::factory()->create();

        // Act: 未認証ユーザーでレビュー編集にアクセス
        $response = $this->get(route('reviews.edit', $Review));

        // Assert: ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }
}
