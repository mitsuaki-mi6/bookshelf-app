<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 登録済みユーザーでログイン時ホームへ遷移()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        // Act: 登録ユーザーでログイン
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(302);
        // 書籍一覧画面へ遷移を確認
        $response->assertRedirect('/');
        // ログイン状態になっていることを確認
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 未登録のメードアドレス時エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        // Act: 登録ユーザーでログイン
        $response = $this->post('/login', [
            'email' => 'othertest@test.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(); // セッションにエラーがあること
        $this->assertGuest(); // ゲスト（未ログイン）のままであること
    }

    /** @test */
    public function 登録メールで未登録パスワード時エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        // Act: 登録ユーザーでログイン
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password999',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(); // セッションにエラーがあること
        $this->assertGuest(); // ゲスト（未ログイン）のままであること
    }
}
