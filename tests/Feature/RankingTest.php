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

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済ユーザーでアクセス時ランキング画面が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成（指定した値で）
        $book1 = Book::factory()->create([
            'user_id' => $user1->id,
            'title' => 'テストランキング１',
            'author' => 'テスト著者１'
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user2->id,
            'title' => 'テストランキング２',
            'author' => 'テスト著者２'
        ]);
        $book3 = Book::factory()->create([
            'user_id' => $user3->id,
            'title' => 'テストランキング３',
            'author' => 'テスト著者３'
        ]);

        // レビューを作成（book_idを紐付ける）
        //　4件、平均:4.66 or 4.7
        $review1 = Review::factory()->create([
            'book_id' => $book1->id,
            'user_id' => $user1->id,
            'rating' => 5,
        ]);
        $review2 = Review::factory()->create([
            'book_id' => $book1->id,
            'user_id' => $user2->id,
            'rating' => 5,
        ]);
        $review3 = Review::factory()->create([
            'book_id' => $book1->id,
            'user_id' => $user3->id,
            'rating' => 4,
        ]);

        //　2件、平均:3.5
        $review4 = Review::factory()->create([
            'book_id' => $book2->id,
            'user_id' => $user1->id,
            'rating' => 4,
        ]);
        $review5 = Review::factory()->create([
            'book_id' => $book2->id,
            'user_id' => $user2->id,
            'rating' => 3,
        ]);

        //　1件、平均:1
        $review6 = Review::factory()->create([
            'book_id' => $book3->id,
            'user_id' => $user3->id,
            'rating' => 1,
        ]);

        // 作成した書籍にジャンルを紐付け
        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);
        $book3->genres()->attach($genre->id);

        // Act: 認証済みユーザーでランキングにアクセス
        $response = $this->actingAs($user1)->get(route('ranking.index'));

        // Assert
        // ランキング画面の表示を確認
        $response->assertStatus(200);
        $response->assertSee('評価ランキング TOP 10');
        $response->assertSee('テストランキング１');
        $response->assertSee('★'); // 星が表示されていることの確認
        $response->assertSeeInOrder([
            'テストランキング１',
            'テストランキング２',
            'テストランキング３',
        ]);     // 評価の高い順に表示されていることを確認
        $response->assertSee('4.66');
        $response->assertSee('3件のレビュー');
        $response->assertSee('4.5');
    }

    /** @test */
    public function 最大10件の表示を確認（11件中10件表示）()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 11件の書籍データを作成
        $books = Book::factory()->count(11)->create();

        foreach ($books as $index => $book) {
            // それぞれの書籍にレビューを紐付ける
            $book->update(['title' => "テスト書籍{$index}"]);

            Review::factory()->create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'rating' => 5, // すべて同じ評価にしておきつつ、タイトルで区別する
            ]);
        }
        // 作成した書籍にジャンルを紐付け
        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }

        // Act: 認証済みユーザーでランキングにアクセス
        $response = $this->actingAs($user)->get(route('ranking.index'));

        // Assert
        // ランキング画面の表示を確認
        $response->assertStatus(200);
        // 1番目〜10番目の書籍名が画面にあることを確認
        $response->assertSee('テスト書籍0'); // （ループのインデックスによる）
        $response->assertSee('テスト書籍9');

        // 11番目の書籍（例: テスト書籍10）が画面に表示されていないことを確認
        $response->assertDontSee('テスト書籍10');
    }

    /** @test */
    public function レビュー投稿件数がゼロ件時ランキング画面が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍データを作成
        $book = Book::factory()->create();

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre);

        // Act: 認証済みユーザーでランキングにアクセス
        $response = $this->actingAs($user)->get(route('ranking.index'));

        // Assert
        // ランキング画面の表示を確認
        $response->assertStatus(200);
        $response->assertSee('まだレビューが投稿された書籍がありません。');
    }

    /** @test */
    public function 未認証（ゲストユーザー）アクセス時ランキング画面が正しく表示される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成（指定した値で）
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テストランキング',
            'author' => 'テスト著者'
        ]);

        // レビューを作成（book_idを紐付ける）
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        // 作成した書籍にジャンルを紐付け
        $book->genres()->attach($genre->id);

        // Act: 未認証（ゲストユーザー）でランキングにアクセス
        $response = $this->get(route('ranking.index'));

        // Assert
        // ランキング画面の表示を確認
        $response->assertStatus(200);
        $response->assertSee('テストランキング');
        $response->assertSee('テスト著者');
        $response->assertSee('1件のレビュー');
        $response->assertSee('5.0');
    }
}
