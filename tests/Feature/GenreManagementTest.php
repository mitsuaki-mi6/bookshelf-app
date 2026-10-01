<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーのジャンル登録処理でジャンルが登録される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/genres', [
            'name' => '新規ジャンル',
        ]);

        // Assert
        $response->assertStatus(302);

        // データベースにジャンルが登録されていることを確認
        $this->assertDatabaseHas('genres', [
            'name' => '新規ジャンル',
        ]);
    }

    /** @test */
    public function 認証済みユーザーのジャンル更新処理でジャンルデータが変更される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '既存ジャンル',
        ]);

        // Act
        $response = $this->actingAs($user)->put("/genres/{$genre->id}", [
            'name' => '更新ジャンル',
        ]);

        // Assert
        $response->assertStatus(302);

        // データベースにジャンルが変更されていることを確認
        $this->assertDatabaseHas('genres', [
            'name' => '更新ジャンル',
        ]);
    }

    /** @test */
    public function 認証済みユーザーのジャンル更新処理でジャンルが存在しない場合は404エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();

        // Act　必須の情報は与えておく
        $response = $this->actingAs($user)->put('/genres/999', [
            'name' => '存在しないジャンルの更新',
        ]);
        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function 認証済みユーザーのジャンル削除処理でジャンルテーブルは削除される()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '既存ジャンル',
        ]);

        // Act
        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        // Assert
        $response->assertStatus(302);

        // データベースにジャンルが削除されていることを確認
        $this->assertDatabaseMissing('genres', [
            'name' => '既存ジャンル',
        ]);
    }

    /** @test */
    public function 認証済みユーザーのジャンル削除処理でジャンルが存在しない場合は404エラー()
    {
        // Arrange
        // ユーザーを作成
        $user = User::factory()->create();
        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '既存ジャンル',
        ]);

        // Act
        $response = $this->actingAs($user)->delete('/genres/999');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function 未認証ユーザーでジャンル登録しようとした場合ログイン画面にリダイレクト()
    {
        // Act: 未認証ユーザーでジャンル登録にアクセス
        $response = $this->post('/genres', [
            'name' => '新規ジャンル',
        ]);

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーでジャンル更新しようとした場合ログイン画面にリダイレクト()
    {
        // Act: 未認証ユーザーでジャンル更新にアクセス
        $response = $this->put('/genres/1', [
            'name' => '更新ジャンル',
        ]);

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーでジャンル削除しようとした場合ログイン画面にリダイレクト()
    {
        // Act: 未認証ユーザーでジャンル削除にアクセス
        $response = $this->delete('/genres/1');

        // Assert: ログイン画面へのリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}
