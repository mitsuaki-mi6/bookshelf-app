<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSortFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ソート条件デフォルト（latest：登録日が新しい順）で表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // タイムスタンプを明確にして古い書籍と新しい書籍を作成
        $bookOld = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);
        $bookNew = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now()->subDays(1),
        ]);

        // Act: パラメータなし（デフォルト）でアクセス
        $response = $this->actingAs($user)->get('/books');

        // Assert デフォルトで新しい書籍が上に表示されていること（並び順の検証）
        $response->assertStatus(200);
        $response->assertSeeInOrder(['新しい書籍', '古い書籍']);
    }

    /** @test */
    public function ソート条件oldest：登録日が古い順で正しい順序で表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // タイムスタンプを明確にして古い書籍と新しい書籍を作成
        $bookOld = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);
        $bookNew = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now()->subDays(1),
        ]);

        // Act: ?sort=oldestでアクセス
        $response = $this->actingAs($user)->get('/books?sort=oldest');

        // Assert 古い書籍が新しい書籍よりも上に表示されていること（並び順の検証）
        $response->assertStatus(200);
        $response->assertSeeInOrder(['古い書籍', '新しい書籍']);
    }

    /** @test */
    public function ソート条件title：タイトル昇順で正しい順序で表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // タイトルソート用の書籍を作成
        $book1 = Book::factory()->create([
            'title' => 'A書籍',
        ]);
        $book2 = Book::factory()->create([
            'title' => 'Z書籍',
        ]);

        // Act: ?sort=titleでアクセス
        $response = $this->actingAs($user)->get('/books?sort=title');

        // Assert タイトル昇順で表示されていること（並び順の検証）
        $response->assertStatus(200);
        $response->assertSeeInOrder(['A書籍', 'Z書籍']);
    }

    /** @test */
    public function ソート条件rating：評価が高い順で正しい順序で表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 評価でソート用の書籍を作成
        $book1 = Book::factory()->create([
            'title' => '評価3書籍',
        ]);
        $book2 = Book::factory()->create([
            'title' => '評価5書籍',
        ]);
        // レビュー情報を紐づけ
        $review1 = Review::factory()->create([
            'book_id' => ($book1->id),
            'rating' => 3,
        ]);
        $review2 = Review::factory()->create([
            'book_id' => ($book2->id),
            'rating' => 5,
        ]);

        // Act: ?sort=ratingでアクセス
        $response = $this->actingAs($user)->get('/books?sort=rating');

        // Assert 評価が高い順で表示されていること（並び順の検証）
        $response->assertStatus(200);
        $response->assertSeeInOrder(['評価5書籍', '評価3書籍']);
    }

    /** @test */
    public function ソート条件rating：評価が高い順の時レビューがない書籍は最後に表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 評価でソート用の書籍を作成
        $book1 = Book::factory()->create([
            'title' => '評価3書籍',
        ]);
        $book2 = Book::factory()->create([
            'title' => '評価5書籍',
        ]);
        $book3 = Book::factory()->create([
            'title' => 'レビューなし書籍',
        ]);
        // レビュー情報を紐づけ
        $review1 = Review::factory()->create([
            'book_id' => ($book1->id),
            'rating' => 3,
        ]);
        $review2 = Review::factory()->create([
            'book_id' => ($book2->id),
            'rating' => 5,
        ]);

        // Act: ?sort=ratingでアクセス
        $response = $this->actingAs($user)->get('/books?sort=rating');

        // Assert 評価が高い順で表示されていること（並び順の検証）
        $response->assertStatus(200);
        $response->assertSeeInOrder(['評価5書籍', '評価3書籍', 'レビューなし書籍']);
    }

    /** @test */
    public function 並び順（sort）が指定外の値の場合、エラー表示()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 書籍を作成
        $book = Book::factory()->create();

        // Act: 指定外の値でアクセス
        $response = $this->actingAs($user)->get('/books?sort=invalid_sort_value');

        // Assert: 無効な並び順が指定された場合にバリデーションエラーとなること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['sort']);
    }
}
