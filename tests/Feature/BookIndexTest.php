<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザーが書籍一覧にアクセスし正しく表示される()
    {
        // Act: ゲストユーザーで書籍一覧にアクセス
        $response = $this->get('/books');

        // Assert: 書籍一覧が表示される_ゲスト
        $response->assertStatus(200);
        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
    }

    /** @test */
    public function 認証済みユーザーが書籍一覧にアクセスし正しく表示される()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーで書籍一覧にアクセス
        $response = $this->actingAs($user)->get('/books');

        // Assert: 書籍一覧が表示される_認証済
        $response->assertStatus(200);
        $response->assertDontSee('ログイン');
        $response->assertDontSee('新規登録');
        $response->assertSee($user->name);
    }

    /** @test */
    public function 書籍にジャンルが複数表示される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);
        // 書籍を作成し、ジャンルを紐付け
        $book = Book::factory()->create();
        $book->genres()->attach([$genre1->id, $genre2->id]); // リレーションの紐付け

        // Act: 認証済みユーザーで書籍一覧にアクセス
        $response = $this->actingAs($user)->get('/books');

        // Assert: 書籍にジャンルが表示される
        $response->assertStatus(200);
        $response->assertSee('小説');
        $response->assertSee('マンガ');
    }

    /** @test */
    public function 書籍10件ごとにページネーションされる_表示件数が11件()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 11件の書籍データを作成
        Book::factory()->count(11)->create();

        // Act: 認証済みユーザーで書籍一覧にアクセス
        $response = $this->actingAs($user)->get('/books');

        // Assert: 書籍にジャンルが表示される
        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10; // 1ページあたりの表示件数が10件であること
        });
        $response->assertSee('?page=2'); // 11件目が2ページ目にあることを示す
    }

    /** @test */
    public function 表示件数がゼロ件()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーで書籍一覧にアクセス
        $response = $this->actingAs($user)->get('/books');

        // Assert: 書籍にジャンルが表示される
        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 0; // 1ページあたりの表示件数が0件であること
        });
        $response->assertSee('書籍が見つかりませんでした。'); // 0件時のメッセージが表示されていること  20261024 修正 MN
    }

    /** @test */
    public function ゲストユーザーでランキングクリックでランキング画面に遷移()
    {
        // Act: ゲストユーザーでランキングにアクセス
        $response = $this->get('/ranking'); // ランキングページのURL

        // Assert: ランキングが表示される_ゲスト
        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストユーザーでログインクリックでログイン画面に遷移()
    {
        // Act: ゲストユーザーでログインにアクセス
        $response = $this->get('/login'); // ログインページのURL

        // Assert: ログイン画面が表示される_ゲスト
        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストユーザーで新規登録クリックで会員登録画面に遷移()
    {
        // Act: ゲストユーザーで新規登録にアクセス
        $response = $this->get('/register'); // 会員登録ページのURL

        // Assert: 会員登録画面が表示される_ゲスト
        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストユーザーで書籍登録クリックでログイン画面にリダイレクト確認()
    {
        // Act: ゲストユーザーで書籍登録にアクセス
        $response = $this->get('/books/create'); // 書籍登録ページのURL

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }

    /** @test */
    public function ゲストユーザーでお気に入りクリックでログイン画面にリダイレクト確認()
    {
        // Act: ゲストユーザーでお気に入りにアクセス
        $response = $this->get('/favorites'); // お気に入りページのURL

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }

    /** @test */
    public function ゲストユーザーでジャンル管理クリックでログイン画面にリダイレクト確認()
    {
        // Act: ゲストユーザーでジャンル管理にアクセス
        $response = $this->get('/genres'); // ジャンル管理ページのURL

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証済みユーザーでランキングクリックでランキング画面に遷移()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーでランキングにアクセス
        $response = $this->actingAs($user)->get('/ranking'); // ランキングページのURL

        // Assert: ランキングが表示される_認証済
        $response->assertStatus(200);
    }

    /** @test */
    public function 認証済みユーザーで書籍登録クリックで書籍登録画面に遷移()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーで書籍登録にアクセス
        $response = $this->actingAs($user)->get('/books/create'); // 書籍登録ページのURL

        // Assert: 書籍登録画面が表示される_認証済
        $response->assertStatus(200);
    }

    /** @test */
    public function 認証済みユーザーでお気に入りクリックでお気に入り画面に遷移()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーでお気に入りにアクセス
        $response = $this->actingAs($user)->get('/favorites'); // お気に入りページのURL

        // Assert: お気に入り画面が表示される_認証済
        $response->assertStatus(200);
    }

    /** @test */
    public function 認証済みユーザーでジャンル管理クリックでジャンル管理画面に遷移()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーでジャンル管理にアクセス
        $response = $this->actingAs($user)->get('/genres'); // ジャンル管理ページのURL

        // Assert: ジャンル管理画面が表示される_認証済
        $response->assertStatus(200);
    }

    /** @test */
    public function 認証済みユーザーでログアウトクリックでログイン画面に遷移()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーでログアウトにアクセス
        $response = $this->actingAs($user)->post('/logout'); // ログアウトページのURL

        // Assert: ログイン画面が表示される_認証済
        $response->assertRedirect('/login');
    }
}
