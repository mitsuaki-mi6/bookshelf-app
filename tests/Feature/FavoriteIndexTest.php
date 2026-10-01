<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済ユーザーでアクセス時お気に入り一覧が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book1 = Book::factory()->create(['title' => 'テスト書籍１']);
        $book2 = Book::factory()->create(['title' => 'テスト書籍２']);
        $book3 = Book::factory()->create(['title' => 'テスト書籍３']);

        // 作成した書籍にジャンルを紐付け
        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);
        $book3->genres()->attach($genre->id);

        // お気に入り登録データを作成
        Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
        ]);

        // Act: 認証済みユーザーでお気に入り一覧にアクセス
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert: お気に入り一覧の表示確認
        $response->assertStatus(200);
        $response->assertSee('お気に入り一覧');
        $response->assertSee('テスト書籍１');
        $response->assertSee('テスト書籍２');
        $response->assertSee('テスト書籍３');
    }

    /** @test */
    public function お気に入り登録した書籍数がゼロの時が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act: 認証済みユーザーでお気に入り一覧にアクセス
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert: お気に入り一覧の表示確認
        $response->assertStatus(200);
        $response->assertSee('お気に入りに登録された書籍はありません。');
    }

    /** @test */
    public function ページネーション確認（お気に入り登録した書籍が11冊の時）()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成
        $books = Book::factory()->count(11)->create();

        // 作成した11冊すべてにジャンルを紐付け、お気に入り登録を行う
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);

            Favorite::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        // Act: 認証済みユーザーでお気に入り一覧にアクセス
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert: お気に入り一覧の表示確認
        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10; // 1ページあたりの表示件数が10件であること
        });
        $response->assertSee('?page=2'); // 11件目が2ページ目にあることを示す
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Act: 未認証ユーザーでお気に入り一覧にアクセス
        $response = $this->get(route('favorites.index'));

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
