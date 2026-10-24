<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ブロックタイトルと基本統計が正しく表示されること()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => 'G1']);
        $genre2 = Genre::factory()->create(['name' => 'G2']);
        $genre3 = Genre::factory()->create(['name' => 'G3']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'title' => '書籍自分評価5_G1',
        ]);
        $book2 = Book::factory()->create([
            'title' => '書籍自分評価4_G2',
        ]);
        $book3 = Book::factory()->create([
            'title' => '書籍自分評価3_G3',
        ]);

        // リレーションの紐付け
        $book1->genres()->attach([$genre1->id]);
        $book2->genres()->attach([$genre2->id]);
        $book3->genres()->attach([$genre3->id]);

        // レビュー情報を紐づけ
        // 自分のレビュー
        $review1 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book1->id),
            'rating' => 5,
        ]);
        $review2 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book2->id),
            'rating' => 4,
        ]);
        $review3 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book3->id),
            'rating' => 3,
        ]);
        $review4_self = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id, // book1の2回目のレビュー
            'rating' => 4,
        ]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        // 読書レポートのブロックタイトルが正常に表示される
        $response->assertStatus(200);
        $response->assertSee('基本統計');
        $response->assertSee('評価分布');
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSee('ジャンル別評価傾向 TOP5');
        $response->assertSee('総レビュー数');
        $response->assertSee('読了冊数');
        $response->assertSee('平均評価');
        // 計算結果などの確認(基本統計)
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 4  // 総レビュー数は自分のレビュー4件のみ
                && $stats['summary']['books_read'] === 3  // 読了冊数は自分のレビューがある書籍3冊のみ
                && (float) $stats['summary']['average_rating'] === 4.0; // 平均評価は(5+4+3+4)/4 = 4.0

        });
    }

    /** @test */
    public function 評価分布が正しく表示されること()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 書籍データを作成
        $book1 = Book::factory()->create(['title' => '評価5の書籍']);
        $book2 = Book::factory()->create(['title' => '評価4の書籍']);
        $book3 = Book::factory()->create(['title' => '評価3の書籍']);
        $book4 = Book::factory()->create(['title' => '評価2の書籍']);
        $book5 = Book::factory()->create(['title' => '評価1の書籍']);

        // 自分のレビュー
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 3]); // book1の2回目のレビュー&評価3×2
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book4->id, 'rating' => 2]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book5->id, 'rating' => 1]);
        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        $response->assertStatus(200);
        $response->assertSee('評価分布');
        // 計算結果などの確認(評価分布)
        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution'][4] === 1  // 5点が1件
                && $stats['rating_distribution'][3] === 1  // 4点が1件
                && $stats['rating_distribution'][2] === 2  // 3点が2件 件数カウントを確認
                && $stats['rating_distribution'][1] === 1  // 2点が1件
                && $stats['rating_distribution'][0] === 1; // 1点が1件
        });
    }

    /** @test */
    public function 高評価書籍_to_p5が正しく表示されること（表示5未満）()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 書籍データを作成
        $book1 = Book::factory()->create(['title' => '評価5書籍1']);
        $book2 = Book::factory()->create(['title' => '評価4.5書籍1']);
        $book3 = Book::factory()->create(['title' => '評価4書籍1']);
        $book4 = Book::factory()->create(['title' => '評価3書籍1']);

        // 自分のレビュー
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]); // book2の2回目のレビュー&評価4
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book4->id, 'rating' => 3]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        $response->assertStatus(200);
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSeeInOrder(['評価5書籍1', '評価4.5書籍1', '評価4書籍1']); // 高評価書籍TOP5ランの並び確認
        $response->assertDontSee('評価3書籍1'); // 評価3の書籍はTOP5に入らないことを確認
    }

    /** @test */
    public function 高評価書籍_to_p5が正しく表示されること（4以上6件）()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 書籍データを作成
        $book1 = Book::factory()->create(['title' => '評価5書籍1']);
        $book2 = Book::factory()->create(['title' => '評価5書籍2']);
        $book3 = Book::factory()->create(['title' => '評価5書籍3']);
        $book4 = Book::factory()->create(['title' => '評価5書籍4']);
        $book5 = Book::factory()->create(['title' => '評価4.5書籍1']);
        $book6 = Book::factory()->create(['title' => '評価4書籍1']);

        // 自分のレビュー
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book4->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book5->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book5->id, 'rating' => 4]); // book5の2回目のレビュー&評価4
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book6->id, 'rating' => 4]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        $response->assertStatus(200);
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSeeInOrder(['評価5書籍1', '評価5書籍2', '評価5書籍3', '評価5書籍4', '評価4.5書籍1']); // 高評価書籍TOP5ランの並び確認
        $response->assertDontSee('評価4書籍1'); // 評価4の書籍はTOP5に入らないことを確認
    }

    /** @test */
    public function 高評価書籍_to_p5に評価4以上が0件の確認()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 書籍データを作成
        $book1 = Book::factory()->create(['title' => '評価3書籍1']);

        // 自分のレビュー
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 3]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        $response->assertStatus(200);
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSee('4星以上の書籍がありません'); // 高評価書籍TOP5に4以上が0件の確認
    }

    /** @test */
    public function ジャンル別評価傾向_to_p5が正しく表示されること()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => 'ジャンル1']);
        $genre2 = Genre::factory()->create(['name' => 'ジャンル2']);
        $genre3 = Genre::factory()->create(['name' => 'ジャンル3']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'title' => '書籍自分評価5_G1',
        ]);
        $book2 = Book::factory()->create([
            'title' => '書籍自分評価4_G2G3',
        ]);
        $book3 = Book::factory()->create([
            'title' => '書籍自分評価3_G3',
        ]);

        // リレーションの紐付け
        $book1->genres()->attach([$genre1->id]);
        $book2->genres()->attach([$genre2->id, $genre3->id]);
        $book3->genres()->attach([$genre3->id]);

        // レビュー情報を紐づけ
        $review1 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book1->id),
            'rating' => 5,
        ]);
        $review2 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book2->id),
            'rating' => 4,
        ]);
        $review3 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book3->id),
            'rating' => 3,
        ]);

        $review4_self = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id, // book1の2回目のレビュー
            'rating' => 4,
        ]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        // 読書レポートのブロックタイトルが正常に表示される
        $response->assertStatus(200);
        $response->assertSee('ジャンル別評価傾向 TOP5');

        // ソート順、計算結果などの確認(ジャンル別評価傾向 TOP5)
        $response->assertSeeInOrder([
            '1',
            'ジャンル1',
            '2件のレビュー', // book1レビュー2件の確認
            '4.5',
            '2',
            'ジャンル2',
            '1件のレビュー',
            '4.0',
            '3',
            'ジャンル3',
            '2件のレビュー', // 1書籍複数ジャンルの確認
            '3.5',
        ]);
    }

    /** @test */
    public function ジャンル６件平均評価_to_p5の確認()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => 'ジャンル1']);
        $genre2 = Genre::factory()->create(['name' => 'ジャンル2']);
        $genre3 = Genre::factory()->create(['name' => 'ジャンル3']);
        $genre4 = Genre::factory()->create(['name' => 'ジャンル4']);
        $genre5 = Genre::factory()->create(['name' => 'ジャンル5']);
        $genre6 = Genre::factory()->create(['name' => 'ジャンル6']);

        // 書籍データを作成
        $book1 = Book::factory()->create([
            'title' => '書籍自分評価5_G1',
        ]);
        $book2 = Book::factory()->create([
            'title' => '書籍自分評価5_G2',
        ]);
        $book3 = Book::factory()->create([
            'title' => '書籍自分評価5_G3',
        ]);
        $book4 = Book::factory()->create([
            'title' => '書籍自分評価4_G4',
        ]);
        $book5 = Book::factory()->create([
            'title' => '書籍自分評価3_G5',
        ]);
        $book6 = Book::factory()->create([
            'title' => '書籍自分評価2_G6',
        ]);

        // リレーションの紐付け
        $book1->genres()->attach([$genre1->id]);
        $book2->genres()->attach([$genre2->id]);
        $book3->genres()->attach([$genre3->id]);
        $book4->genres()->attach([$genre4->id]);
        $book5->genres()->attach([$genre5->id]);
        $book6->genres()->attach([$genre6->id]);

        // レビュー情報を紐づけ
        $review1 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book1->id),
            'rating' => 5,
        ]);
        $review2 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book2->id),
            'rating' => 5,
        ]);
        $review3 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => ($book3->id),
            'rating' => 5,
        ]);
        $review4 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book4->id,
            'rating' => 4,
        ]);
        $review5 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book5->id,
            'rating' => 3,
        ]);
        $review6 = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book6->id,
            'rating' => 2,
        ]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        // 読書レポートのブロックタイトルが正常に表示される
        $response->assertStatus(200);
        $response->assertSee('ジャンル別評価傾向 TOP5');
        // 表示されるジャンルの確認
        $response->assertSee('ジャンル1');
        $response->assertSee('ジャンル5');
        // 6件目のジャンルはTOP5に入らないことを確認
        $response->assertDontSee('ジャンル6');
    }

    /** @test */
    public function レビュー投稿ゼロの時()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍データを作成
        $book = Book::factory()->create();

        // リレーションの紐付け
        $book->genres()->attach([$genre->id]);

        // Act: 認証済みユーザーでマイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');

        // Assert　
        // 読書レポートのブロックタイトルが正常に表示される
        $response->assertStatus(200);
        $response->assertSee('ジャンル別評価傾向 TOP5');
        // 評価されたジャンルがないことを確認
        $response->assertSee('ジャンルが設定された書籍のレビューがありません');
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Act: 未認証ユーザーでマイ読書レポートにアクセス
        $response = $this->get('/reports');

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 高評価書籍_to_p5書籍リンククリック時に正常に遷移する時()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create();
        // 書籍データを作成
        $book = Book::factory()->create();
        // リレーションの紐付け
        $book->genres()->attach([$genre->id]);
        // 高評価書籍TOP5に表示させるためのレビューを作成しておく
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        // Act1 マイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');
        // Assert1 書籍詳細へのリンクが存在することを確認
        $response->assertStatus(200);
        $response->assertSee("/books/{$book->id}");

        // Act2 実際にその詳細ページにアクセスして200が返ることを確認
        $detailResponse = $this->actingAs($user)->get("/books/{$book->id}");
        // Assert2  書籍詳細画面が表示されることを確認
        $detailResponse->assertStatus(200);
    }

    /** @test */
    public function ジャンル別評価傾向_to_p5のジャンルリンククリック時に正常に遷移する()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create();
        // 書籍データを作成
        $book = Book::factory()->create();
        // リレーションの紐付け
        $book->genres()->attach([$genre->id]);
        // レビューを紐づけ
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act1 マイ読書レポートにアクセス
        $response = $this->actingAs($user)->get('/reports');
        // Assert1 ジャンルの一覧へのリンクが存在することを確認
        $response->assertStatus(200);
        $response->assertSee("/genres/{$genre->id}");

        // Act2 実際にジャンル一覧ページにアクセスして200が返ることを確認
        $detailResponse = $this->actingAs($user)->get("/genres/{$genre->id}");
        // Assert2  ジャンルの一覧画面が表示されることを確認
        $detailResponse->assertStatus(200);
    }
}
