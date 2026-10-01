<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済ユーザーでアクセス時ジャンル詳細が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create(['name' => 'テストジャンル']);

        // 書籍を作成
        $book1 = Book::factory()->create(['title' => 'テスト書籍１']);
        $book2 = Book::factory()->create(['title' => 'テスト書籍２']);
        $book3 = Book::factory()->create(['title' => 'テスト書籍３']);

        // 作成した書籍にジャンルを紐付け
        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);
        $book3->genres()->attach($genre->id);

        // Act: 認証済みユーザーでジャンル詳細にアクセス
        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        // Assert: ジャンル詳細の表示確認
        $response->assertStatus(200);
        $response->assertSee('ジャンル: テストジャンル');
        $response->assertSee('テスト書籍１');
        $response->assertSee('テスト書籍２');
        $response->assertSee('テスト書籍３');
    }

    /** @test */
    public function ジャンルに紐付いている書籍数がゼロの時が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create(['name' => 'テストジャンル']);

        // Act: 認証済みユーザーでジャンル詳細にアクセス
        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        // Assert: ジャンル詳細の表示確認
        $response->assertStatus(200);
        $response->assertSee('ジャンル: テストジャンル');
        $response->assertSee('このジャンルの書籍はまだ登録されていません。');
    }

    /** @test */
    public function ページネーション確認（紐づいている書籍が11冊の時）()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成
        Book::factory()->count(11)->create();

        // 作成した書籍にジャンルを紐付け
        $books = Book::all();
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act: 認証済みユーザーでジャンル詳細にアクセス
        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        // Assert: ジャンル詳細の表示確認
        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10; // 1ページあたりの表示件数が10件であること
        });
        $response->assertSee('?page=2'); // 11件目が2ページ目にあることを示す
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Act: 未認証ユーザーでジャンル詳細にアクセス
        $response = $this->get(route('genres.show', 1));

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
