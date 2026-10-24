<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワード・ジャンルフィルタが機能し条件に一致する書籍が表示される(): void
    {
        // Arrange
        // 管理者ユーザーと、検索ヒットする/しないテストデータを用意
        $user = User::factory()->create();
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => '辞典']);

        // 検索条件に合致するデータ
        $book1 = Book::factory()->create([
            'title' => '検索ヒット書籍',
            'author' => '検索ヒット著者',
        ]);

        // 検索条件に合致しないデータ
        $book2 = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
        $book3 = Book::factory()->create([
            'title' => 'テスト書籍２',
            'author' => 'テスト著者２',
        ]);

        // 作成した書籍にジャンルを紐付け
        $book1->genres()->attach([$genre1->id]);
        $book2->genres()->attach([$genre2->id]);
        $book3->genres()->attach([$genre2->id]);

        // Act
        // 検索条件でアクセス
        $response = $this->actingAs($user)->get('/books?keyword=検索&genre='.$genre1->id);

        // Assert
        // ヒットデータが表示される
        $response->assertStatus(200);
        $response->assertSee('検索ヒット書籍');
        // ヒットデータなし
        $response->assertDontSee('テスト書籍');
        $response->assertDontSee('テスト書籍２');
    }

    /** @test */
    public function 結果の表示は10件ごとにページネーションされる（11冊目は2ページ目に表示される）(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => '辞典']);

        // 検索条件に合致するデータを11冊作成（タイトルで区別できるようにする）
        $books = collect();
        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'title' => "ヒット小説書籍{$i}",
            ]);
            $book->genres()->attach([$genre1->id]);
            $books->push($book);
        }

        // 検索条件に合致しないデータ（別ジャンル）を1冊作成
        $noHitBook = Book::factory()->create([
            'title' => 'テスト辞典書籍',
        ]);
        $noHitBook->genres()->attach([$genre2->id]);

        // Act (1ページ目)
        $response = $this->actingAs($user)->get('/books?genre='.$genre1->id);

        // Assert (1ページ目)
        $response->assertStatus(200);
        // 1〜10冊目が表示されていること
        $response->assertSee('ヒット小説書籍1');
        $response->assertSee('ヒット小説書籍10');
        // 11冊目は1ページ目には表示されないこと
        $response->assertDontSee('ヒット小説書籍11');
        $response->assertDontSee('ヒット辞典書籍');

        // 1ページあたりの表示件数が10件であることの確認
        $response->assertViewHas('books', function ($booksCollection) {
            return $booksCollection->count() === 10;
        });

        // Act (2ページ目)
        $responsePage2 = $this->actingAs($user)->get('/books?genre='.$genre1->id.'&page=2');

        // Assert (2ページ目)
        $responsePage2->assertStatus(200);
        // 2ページ目に11冊目が表示されていること
        $responsePage2->assertSee('ヒット小説書籍11');
        // 1ページ目の書籍は2ページ目には表示されないこと
        $responsePage2->assertDontSee('ヒット小説書籍2');
    }

    /** @test */
    public function 表示件数（ヒット件数）がゼロ件(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => '辞典']);

        // 検索条件に合致しないデータを作成（タイトルで区別できるようにする）
        $books = collect();
        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'title' => "ヒット小説書籍{$i}",
            ]);
            $book->genres()->attach([$genre1->id]);
            $books->push($book);
        }

        // Act
        $response = $this->actingAs($user)->get('/books?genre='.$genre2->id);

        // Assert
        $response->assertStatus(200);
        $response->assertSee('書籍が見つかりませんでした。');
        // ビューに渡される books が空（件数0）であることを確認する場合
        $response->assertViewHas('books', function ($booksCollection) {
            return $booksCollection->count() === 0;
        });
    }

    /** @test */
    public function ページを変更しても検索条件が維持される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '技術書']);

        // 検索にヒットする書籍を12冊作成してページネーションを発生させる
        $books = collect();
        for ($i = 1; $i <= 12; $i++) {
            $book = Book::factory()->create([
                'title' => "TEST技術書{$i}",
            ]);
            $book->genres()->attach([$genre->id]);
            $books->push($book);
        }

        // Act
        // 2ページ目に検索パラメータ付きでアクセス
        $response = $this->actingAs($user)->get('/books?keyword=TEST&genre='.$genre->id.'&page=2');

        // Assert
        $response->assertStatus(200);
        // ページネーションのリンクやフォームにパラメータ（keywordやgenre）が保持されていること
        $response->assertSee('keyword=TEST');
        $response->assertSee('genre='.$genre->id);
    }
}
