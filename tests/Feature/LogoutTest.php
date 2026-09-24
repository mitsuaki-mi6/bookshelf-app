<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーのログアウトでログイン画面にリダイレクトされる()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // Act: ログインユーザでログアウト
        $response = $this->actingAs($user)->post("/logout");

        // Assert
        $response->assertStatus(302);
        // ログイン画面へ遷移を確認
        $response->assertRedirect('/login');
        $this->assertGuest(); // 未ログインであること
    }
}
