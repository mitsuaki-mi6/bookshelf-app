<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ID、passが正常な値で入力されてパスできること()
    {
        // Arrange
        // ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        // Act
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    /** @test */
    public function 必須項目の未入力を拒否する()
    {
        // Arrange
        // ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        // Act
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);
        // Assert
        $response->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function 不正なメール形式を拒否する()
    {
        // Arrange
        // ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        // Act
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);
        // Assert
        $response->assertSessionHasErrors(['email']);
    }
}
