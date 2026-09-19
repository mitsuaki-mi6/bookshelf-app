<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;

class GenreUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * リクエストにルートパラメータをバインドするためのヘルパーメソッド
     */
    private function createRequestWithRoute($genreId)
    {
        $request = UpdateGenreRequest::create('/genres/' . $genreId, 'PUT');
        $route = new Route('PUT', 'genres/{genre}', [UpdateGenreRequest::class]);
        $route->bind($request);
        $route->setParameter('genre', $genreId);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        return $request;
    }

    /** @test */
    public function 必須項目が正常な値で入力されて登録できること()
    {
        // Arrange
        $genre = Genre::create([
            'name' => '既存のジャンル',
        ]);

        $request = $this->createRequestWithRoute($genre->id);

        $data = [
            'name' => 'テストジャンル',
        ];

        // Act
        $validator = \Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 自分と同じジャンル名が存在する場合登録できる()
    {
        // Arrange
        $genre = Genre::create([
            'name' => '既存のジャンル',
        ]);

        $request = $this->createRequestWithRoute($genre->id);

        $data = [
            'name' => '既存のジャンル',
        ];

        // Act
        $validator = \Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 必須項目の未入力を拒否する()
    {
        // Arrange
        $genre = Genre::create([
            'name' => '既存のジャンル',
        ]);

        $request = $this->createRequestWithRoute($genre->id);

        $data = [
            'name' => '',
        ];

        // Act
        $validator = \Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function ジャンル名の文字数上限（255文字）を拒否する()
    {
        // Arrange
        $genre = Genre::create([
            'name' => '既存のジャンル',
        ]);

        $request = $this->createRequestWithRoute($genre->id);

        $data = [
            'name' => str_repeat('あ', 256),
        ];

        // Act
        $validator = \Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function 同じジャンル名が存在する場合を拒否する_自分以外()
    {
        // Arrange
        $myGenre = Genre::create(['name' => '自分のジャンル']);
        Genre::create(['name' => '他のジャンル']);

        $request = $this->createRequestWithRoute($myGenre->id);

        // 1. 自分自身の名前で更新する場合
        $dataSelf = [
            'name' => '自分のジャンル',
        ];

        $validatorSelf = \Validator::make($dataSelf, $request->rules());
        $this->assertFalse($validatorSelf->fails(), '自分自身の名前への変更は許可されるべきです。');

        // 2. 他のジャンル名と重複させて更新する場合
        $dataOther = [
            'name' => '他のジャンル',
        ];

        $validatorOther = \Validator::make($dataOther, $request->rules());
        $this->assertTrue($validatorOther->fails(), '他のジャンル名との重複は拒否されるべきです。');
        $this->assertArrayHasKey('name', $validatorOther->errors()->toArray());
    }
}
