<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookCreateScreenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済ユーザーのアクセス時書籍登録画面が正しく表示される()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);

        // Act: 認証済みユーザーで書籍登録にアクセス
        $response = $this->actingAs($user)->get(route('books.create'));

        // Assert
        // 書籍登録画面の表示を確認
        $response->assertStatus(200);
        // 入力項目ラベル確認
        $response->assertSee('タイトル');
        $response->assertSee('著者');
        $response->assertSee('ISBN-13');
        $response->assertSee('出版日');
        $response->assertSee('画像URL');
        $response->assertSee('ジャンル');
        $response->assertSee('説明');
        $response->assertSee('*'); // 必須項目の表示確認

        // 登録されているジャンル名とそれぞれのチェックボックスの表示確認
        $response->assertSee('小説');
        $response->assertSee('value="' . $genre1->id . '"', false);

        $response->assertSee('マンガ');
        $response->assertSee('value="' . $genre2->id . '"', false);

        // ボタン
        $response->assertSee('キャンセル');
        $response->assertSee('登録');
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクト確認()
    {
        // Act: 未認証ユーザーで書籍登録にアクセス
        $response = $this->get('/books/create'); // 書籍登録ページのURL

        // Assert: ログイン画面にリダイレクトされる_ゲスト
        $response->assertRedirect('/login');
    }
}
