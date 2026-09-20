<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginScreenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザーがログイン画面にアクセスし正しく表示される()
    {
        // Act: ゲストユーザーでログイン画面アクセス
        $response = $this->get('/login');

        // Assert: ログイン画面が表示される
        $response->assertStatus(200);
        $response->assertSee('email');
        $response->assertSee('password');
    }

    /** @test */
    public function 認証済みユーザーがログイン画面にアクセスした場合、ホーム画面等にリダイレクトされる()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーでログイン画面アクセス
        $response = $this->actingAs($user)->get('/login');

        // Assert: ホーム画面等にリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
