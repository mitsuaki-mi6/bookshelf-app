<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;

class GenreModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つのジャンルから紐づく複数の書籍が正しく取得できる()
    {
        // Arrange
        // ユーザ情報のセット
        $user = User::factory()->create();

        // ジャンルを1つ作成し、
        $genre = Genre::factory()->create();
        // それに紐づく書籍を3つ作成する
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id
        ]);
        // 中間テーブルや外部キーなどを介してジャンルと書籍を紐付ける
        $genre->books()->attach($books->pluck('id'));

        // Act
        // book_genreのリレーションを通じて書籍を取得する
        $results = $genre->books;

        // Assert
        // 取得した数が3つであり、かつIDが一致していることを確認する
        $this->assertCount(3, $results);
        foreach ($books as $index => $book) {
            $this->assertEquals($book->id, $results[$index]->id);
        }
    }
}
