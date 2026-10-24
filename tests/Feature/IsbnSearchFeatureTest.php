<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function isbnに正常な値を入力し検索の結果フォームに自動入力される()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 正しいISBNを指定
        $isbn = '9784873117584';
        // Google Books APIのレスポンスをモック化
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'AWSではじめる クラウド開発入門',
                            'authors' => ['真野智之'],
                            'publishedDate' => '2021-09-27',
                            'description' => '※この商品はタブレットなど大きいディスプレイを備えた端末で読むことに適しています。',
                            'imageLinks' => [
                                'thumbnail' => 'http://books.google.com/books/content?id=AIhFEAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);
        // Act: パラメータなし（デフォルト）でアクセス
        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        // Assert APIから取得した書籍情報が正しくレスポンスに含まれていること(JSONデータ)
        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'AWSではじめる クラウド開発入門',
            'author' => '真野智之',
            'published_date' => '2021-09-27',
            'description' => '※この商品はタブレットなど大きいディスプレイを備えた端末で読むことに適しています。',
            'image_url' => 'http://books.google.com/books/content?id=AIhFEAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api',
        ]);
    }

    /** @test */
    public function isbnに存在しない場合データ無し表示()
    {
        // Arrange
        // 認証済みユーザーを作成
        $user = User::factory()->create();
        // 存在しないISBNを指定
        $isbn = '9784873117585';

        // Google Books APIがヒットしなかった（itemsが空）場合のモック
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        // Act: パラメータなし（デフォルト）でアクセス
        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        // Assert ISBNに存在しない場合
        $response->assertStatus(404);
        $response->assertJson(['error' => '書籍が見つかりませんでした。']);
    }

    /** @test */
    public function ISBNが13桁以外の時は400エラーが返ること()
    {
        // Arrange
        $user = User::factory()->create();
        $invalidIsbn = '12345'; // 13桁以外

        // Act
        $response = $this->actingAs($user)->get("/books/isbn/{$invalidIsbn}");

        // Assert
        $response->assertStatus(400);
        $response->assertJson(['error' => 'ISBNは13桁で入力してください。']);
    }

    /** @test */
    public function GoogleBooksAPIのクォータ超過の時は429エラーが返ること()
    {
        // Arrange
        $user = User::factory()->create();
        $isbn = '9784873117584';

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 429),
        ]);

        // Act
        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        // Assert
        $response->assertStatus(429);
        $response->assertJson(['error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。']);
    }

    /** @test */
    public function API通信エラーの時は500エラーが返ること()
    {
        // Arrange
        $user = User::factory()->create();
        $isbn = '9784873117584';

        // 例外をスローさせてcatchブロック（500エラー）を発動させる
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException();
            },
        ]);

        // Act
        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        // Assert
        $response->assertStatus(500);
        $response->assertJson(['error' => 'API通信エラーが発生しました。']);
    }
}
