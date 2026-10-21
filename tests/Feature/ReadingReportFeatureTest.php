<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
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
                && (float)$stats['summary']['average_rating'] === 4.0; // 平均評価は(5+4+3+4)/4 = 4.0

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
}
