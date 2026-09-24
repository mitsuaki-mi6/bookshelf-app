<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Favorite;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーのお気に入り登録でお気に入りが登録される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("/books/{$book->id}/favorites");

        // Assert:
        $response->assertStatus(302);
        // データベースにお気に入りが登録されていることを確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーのお気に入り解除でお気に入りが削除される()
    {
        // Arrange: ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // お気に入りを作成
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act
        $response = $this->actingAs($user)->post("/books/{$book->id}/favorites");

        // Assert:
        $response->assertStatus(302);
        // お気に入りが削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーでお気に入り登録または解除にアクセス時ログイン画面にリダイレクト()
    {
        // Act
        $response = $this->post("/books/1/favorites");

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
