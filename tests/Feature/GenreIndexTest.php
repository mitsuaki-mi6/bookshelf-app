<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class GenreIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済ユーザーでアクセス時ジャンル一覧画面が正しく表示される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'マンガ']);
        $genre3 = Genre::factory()->create(['name' => '辞典']);

        // 書籍を作成
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        // 作成した書籍にジャンルを紐付け
        $book1->genres()->attach([$genre1->id, $genre2->id]);
        $book2->genres()->attach([$genre1->id]);

        // Act: 認証済みユーザーでジャンル一覧にアクセス
        $response = $this->actingAs($user)->get(route('genres.index'));

        // Assert
        $response->assertStatus(200);
        // ジャンル一覧情報の表示を確認
        $response->assertSee('ジャンル管理');
        $response->assertSee('小説');
        $response->assertSee('マンガ');
        $response->assertSee('辞典');
        $response->assertSee('2冊');
        $response->assertSee('1冊');
        $response->assertSee('0冊');
        $response->assertSee('ジャンルを登録');
        $response->assertSee('編集');
        $response->assertSee('削除');
    }

    /** @test */
    public function 未認証でアクセス時ログイン画面にリダイレクトされる()
    {
        // Act: 未認証ユーザーでジャンル一覧にアクセス
        $response = $this->get(route('genres.index'));

        // Assert: ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }
}
