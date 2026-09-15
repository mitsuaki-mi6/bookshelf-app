<?php

namespace Tests\Feature\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること(): void
    {
        $creator = new CreateNewUser();
        // Arrange
        $user = $creator->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function 必須項目の未入力を拒否する(): void
    {
        $this->expectException(ValidationException::class);

        $creator = new CreateNewUser();
        $creator->create([
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);
    }

    /** @test */
    public function 不正なメール形式を拒否する(): void
    {
        $this->expectException(ValidationException::class);

        $creator = new CreateNewUser();
        $creator->create([
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    /** @test */
    public function パスワード8文字以下を拒否する(): void
    {
        $this->expectException(ValidationException::class);

        $creator = new CreateNewUser();
        $creator->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ]);
    }

    /** @test */
    public function パスワード不一致を拒否する(): void
    {
        $this->expectException(ValidationException::class);

        $creator = new CreateNewUser();
        $creator->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);
    }
}
