<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Book;
use Tests\TestCase;

class IsbnSearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ISBNに正常な値を入力し検索の結果フォームに自動入力される()
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
    public function ISBNに存在しない場合データ無し表示()
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
        $response->assertJson(['error' => '書籍情報が見つかりませんでした。']);
    }
}
