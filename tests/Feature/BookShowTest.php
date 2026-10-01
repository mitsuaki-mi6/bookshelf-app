<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    //　ゲストユーザーでアクセスしたときのテスト
    /** @test */
    public function ゲストユーザーで書籍詳細にアクセスし正しく表示される()
    {
        // Arrange
        // ユーザーを用意する（既存のUserファクトリーを使用）
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // レビューを作成（指定した値で）
        // レビュー1つ目を作成（book_idを紐付ける）
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => 'テストレビュー内容1',
        ]);

        // レビュー2つ目を作成（同じ書籍のIDを紐付ける）
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'テストレビュー内容2',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach([$genre1->id, $genre2->id]);

        // Act: ゲストユーザーで書籍詳細画面にアクセス（動的にURLを生成すると安全です）
        $response = $this->get(route('books.show', $book));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('1234567890123');
        $response->assertSee('1999-05-07');
        $response->assertSee('テスト説明');
        $response->assertSee('https://example.com/image.jpg');
        $response->assertSee('小説');
        $response->assertSee('マンガ');
        // レビュー情報の表示を確認
        $response->assertSee('テストレビュー内容1');
        $response->assertSee('★☆☆☆☆');
        $response->assertSee('テストレビュー内容2');
        $response->assertSee('★★★★☆');
        // お気に入りボタンが「お気に入りに追加」（OFF状態）として存在しているか
        $response->assertSee('お気に入りに追加');
        // いいねが「いいね」（未登録状態）として存在しているか（「いいね済み」ではないこと）
        $response->assertSee('いいね');
        // 「いいね済み」が存在しない（ON状態）ことの確認
        $response->assertDontSee('いいね済み');
        // ゲスト用のログイン誘導やボタンの表示確認
        $response->assertSee('ログイン');
    }

    /** @test */
    public function ゲストユーザーでお気に入りクリックでログイン画面にリダイレクト確認()
    {
        // Arrange: テスト用の書籍を1冊用意する
        $book = Book::factory()->create();

        // Act: ゲストユーザーでお気に入りクリック
        $response = $this->post('/books/'.$book->id.'/favorites');

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }

    /** @test */
    public function ゲストユーザーでいいねクリックでログイン画面にリダイレクト確認()
    {
        // Arrange: ユーザー、書籍、レビューをそれぞれ用意する
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // Act: ゲストユーザーでいいねクリック
        $response = $this->post('/reviews/'.$review->id.'/like');

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }

    // 認証済認可済ユーザーのテスト
    /** @test */
    public function 認証済認可済ユーザーのアクセス時書籍情報が正しく表示される()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create(); // ← 他人用のユーザー

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // レビューを作成（指定した値で）
        // レビュー1つ目を作成（book_idを紐付ける）
        $myReview = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id, // ログイン中のユーザーIDを使用
            'rating' => 1,
            'comment' => '自分のテストレビュー',
        ]);
        // レビュー2つ目を作成（book_idを紐付ける）
        $otherReview = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id, // 別のユーザーIDを使用
            'rating' => 5,
            'comment' => '他人のテストレビュー',
        ]);

        // 他人のテストレビュー」に対して、自分と別の人が「いいね」
        // これにより、レビューのいいね数が「2」になる
        ReviewLike::factory()->create([
            'review_id' => $otherReview->id,
            'user_id' => $user->id, // ログイン中のユーザーがいいね（ON状態のテスト）
        ]);
        ReviewLike::factory()->create([
            'review_id' => $otherReview->id,
            'user_id' => $otherUser->id, // 他のユーザーもいいね（合計で2いいね）
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach([$genre1->id, $genre2->id]);

        // Act: 認証済みユーザーで書籍詳細にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('1234567890123');
        $response->assertSee('1999-05-07');
        $response->assertSee('テスト説明');
        $response->assertSee('https://example.com/image.jpg');
        $response->assertSee('小説');
        $response->assertSee('マンガ');
        $response->assertSee(route('books.edit', $book)); // 書籍編集ボタンがあることを示す
        $response->assertSee(route('books.destroy', $book)); // 書籍削除ボタンが画面にあることを確認
        $response->assertSee('お気に入りに追加'); // お気に入りボタンが「お気に入りに追加」（OFF状態）として存在しているか

        // レビュー投稿エリアの表示確認
        $response->assertSee('レビューを投稿'); // セクションの見出しタイトル
        $response->assertSee('投稿する');       // 送信ボタンのテキスト

        // レビュー情報の表示を確認
        // 自分のレビューが正しく表示されることを確認
        $response->assertSee('自分のテストレビュー'); // 自分のレビュー内容が表示されることを確認
        $response->assertSee('★☆☆☆☆'); // 自分のレビュー評価が表示されることを確認
        $response->assertSee(route('reviews.edit', $myReview)); // レビュー編集ボタンがあることを示す
        // いいねが未登録状態として存在しているか（「いいね済み」ではないこと）
        $response->assertSee('いいね');
        $response->assertSee(route('reviews.destroy', $myReview)); // 自分のレビュー削除があることを確認

        // 他人のレビューが正しく表示されることを確認
        $response->assertSee('他人のテストレビュー'); // 他人のレビュー内容が表示されることを確認
        $response->assertSee('★★★★★'); // 他人のレビュー評価が表示されることを確認
        $response->assertDontSee(route('reviews.edit', $otherReview)); // 他人のレビューの編集リンクは「表示されていない」こと
        $response->assertSee('いいね済み (2)'); // いいね済みの表示があることを確認
    }

    /** @test */
    public function 認証済認可済ユーザーのアクセス時お気に入り_o_nが正しく表示されている()
    {
        // Arrange
        $user = User::factory()->create(); // 認証済みユーザーを作成
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
        // お気に入り登録データを作成
        Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーで書籍詳細にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee(route('books.edit', $book)); // 書籍編集ボタンがあることを示す
        $response->assertSee('お気に入りから削除'); // お気に入りボタンが「お気に入りから削除」（ON状態）として存在しているか
    }

    /** @test */
    public function 認証済認可済ユーザーのアクセス時画像_ur_l無しでも書籍情報が正しく表示される()
    {
        // Arrange
        $user = User::factory()->create(); // 認証済みユーザーを作成
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => null, // 画像URLをnullに設定
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーで書籍詳細にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('画像なし'); // 画像URLがnullの場合の表示確認
    }

    /** @test */
    public function 認証済認可済ユーザーのアクセス時レビュー投稿がゼロ件()
    {
        // Arrange
        $user = User::factory()->create(); // 認証済みユーザーを作成
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーで書籍詳細にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('まだレビューはありません。'); // レビューが投稿されていない場合の表示確認
    }

    /** @test */
    public function データベースに存在しない書籍_i_dを指定してアクセスした場合に、404が返される()
    {
        // Arrange
        $user = User::factory()->create(); // 認証済みユーザーを作成

        // Act: 認証済みユーザーで存在しない書籍詳細にアクセス
        $response = $this->actingAs($user)->get('books/9999'); // 存在しない書籍IDを指定

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(404);
    }

    /** @test */
    public function 認証済認可無しユーザーが書籍詳細にアクセスし正しく表示される_編集・削除ボタン無し()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();
        $otherUser = User::factory()->create(); // ← 他人用のユーザー
        $genre = Genre::factory()->create(); // ジャンルを作成

        // 他人が登録した書籍を作成
        $otherbook = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '1999-05-07',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        // 作成した書籍にジャンルを紐付け
        $otherbook->genres()->attach($genre);

        // Act: 認証済みユーザーで書籍詳細にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $otherbook));

        // Assert
        // 書籍詳細情報の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertDontSee(route('books.edit', $otherbook)); // 編集ボタンがない
        $response->assertDontSee('削除'); // 「削除」という文字が画面にないこと
    }
}
