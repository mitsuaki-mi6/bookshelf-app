<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegistrationScreenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザーが会員登録画面にアクセスし正しく表示される()
    {
        // Act: ゲストユーザーで会員登録画面アクセス
        $response = $this->get('/register');

        // Assert: 会員登録画面が表示される
        $response->assertStatus(200);
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee('password_confirmation');
    }

    /** @test */
    public function 認証済みユーザーが会員登録画面にアクセスした場合、ホーム画面等にリダイレクトされる()
    {
        // Arrange: 認証済みユーザーを作成
        $user = User::factory()->create();

        // Act: 認証済みユーザーで会員登録画面アクセス
        $response = $this->actingAs($user)->get('/register');

        // Assert: ホーム画面等にリダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
