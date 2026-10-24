<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画から紐づくユーザーが正しく取得できること()
    {
        // Arrange
        // ユーザとそれに紐づく書籍・読書計画を用意する
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $reading_plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act
        // 読書計画からユーザーを取得する
        $results = $reading_plan->user;

        // Assert
        // 取得したユーザーのIDが一致していることを確認する
        $this->assertInstanceOf(User::class, $results);
        $this->assertEquals($user->id, $results->id);
    }

    /** @test */
    public function 読書計画に紐付く書籍が正しく取得できること()
    {
        // Arrange
        // ユーザとそれに紐づく書籍・読書計画を用意する
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $reading_plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act
        // 読書計画から書籍を取得する
        $results = $reading_plan->book;

        // Assert
        // 取得した書籍のIDが一致していることを確認する
        $this->assertInstanceOf(Book::class, $results);
        $this->assertEquals($book->id, $results->id);
    }

    /** @test */
    public function statusが_reading_plan_statusにキャストされ値が一致すること()
    {
        // Enumキャストの検証
        $reading_plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->assertInstanceOf(ReadingPlanStatus::class, $reading_plan->status);
        $this->assertEquals(ReadingPlanStatus::IN_PROGRESS, $reading_plan->status);
        $this->assertEquals('進行中', $reading_plan->status->label());
    }
}
