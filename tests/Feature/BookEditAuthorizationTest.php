<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookEditAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証認可済ユーザーでアクセス時書籍編集画面が正しく表示される()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);
        $genre3 = Genre::factory()->create(['name' => '辞典']);

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

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach([$genre1->id, $genre2->id]);

        // Act: 認証済みユーザーで書籍編集にアクセス
        $response = $this->actingAs($user)->get(route('books.edit', $book));

        // Assert
        // 書籍編集情報の表示を確認
        // 入力項目ラベル確認
        $response->assertStatus(200);
        $response->assertSee('既登録テスト書籍');
        $response->assertSee('既登録テスト著者');
        $response->assertSee('1234567890123');
        $response->assertSee('1999-05-07');
        $response->assertSee('既登録テスト説明');
        $response->assertSee('https://example.com/image.jpg');

        // 登録されているジャンル名とそれぞれの紐づきの確認
        $response->assertViewHas('book', function ($book) use ($genre1, $genre2, $genre3) {
            return $book->genres->contains($genre1->id)  // 小説：紐付いている（チェックあり）
                && $book->genres->contains($genre2->id)  // マンガ：紐付いている（チェックあり）
                && ! $book->genres->contains($genre3->id); // 辞典：紐付いていない（チェックなし）
        });

        // ボタン
        $response->assertSee('キャンセル');
        $response->assertSee('更新');
    }

    /** @test */
    public function 認証済未認可でアクセス時403エラー()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create(); // ← 他人用のユーザー
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 他人が登録した書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーで他人の書籍編集にアクセス
        $response = $this->actingAs($user)->get(route('books.edit', $book));

        // Assert
        // 他人の書籍のため、403エラー（アクセス拒否）が返ることを確認
        $response->assertStatus(403);
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Arrange: 書籍データを作成
        $book = Book::factory()->create();

        // Act: 未認証ユーザーで書籍編集にアクセス
        $response = $this->get(route('books.edit', $book));

        // Assert: ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }
}
