<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザの会員登録処理で会員情報が登録される()
    {
        // Act: 未認証ユーザー会員情報を登録
        $response = $this->post('/register', [
            'name' => '新規ユーザ',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert
        $response->assertStatus(302);
        // データベースにユーザーが保存されていること
        $this->assertDatabaseHas('users', [
            'name' => '新規ユーザ',
            'email' => 'test@test.com',
        ]);

        // 書籍一覧画面へ遷移を確認
        $response->assertRedirect('/');

        // 認証済みの状態になっていること
        $this->assertAuthenticated();
    }
}
