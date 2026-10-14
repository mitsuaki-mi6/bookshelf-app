<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReadingReportController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// 書籍関係
// PG01 書籍一覧（トップ）
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// ISBN検索
Route::get('/books/isbn/{isbn}', [BookController::class, 'searchIsbn'])->name('books.searchIsbn');

Route::middleware('auth')->group(
    function () {
        // PG03 書籍登録
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
    }
);

// PG02 書籍詳細
// 書籍詳細表示
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// PG11 ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ----------------------------------------------------------------
// ログインユーザーのみアクセスできるルート（認証必須）
// ----------------------------------------------------------------
Route::middleware('auth')->group(
    function () {
        // PG04 書籍編集・削除
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

        // お気に入り切り替え
        Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        // レビュー投稿
        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        // PG09	レビュー編集
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

        // レビュー関連（いいね）
        Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

        // レビュー関連（編集）
        Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

        // レビュー関連（削除）
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // PG10 お気に入り一覧
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

        // ジャンル関係
        // PG05	ジャンル一覧
        Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

        // PG07	ジャンル登録
        Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
        Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');

        // PG06	ジャンル詳細
        Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

        // PG08	ジャンル編集
        Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
        Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');

        // ジャンル削除
        Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');


        // 読書計画関係
        // PG15 読書計画一覧
        Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');

        // PG16	読書計画作成処理
        Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
        Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');

        // PG17	読書計画編集処理
        Route::get('/reading-plans/{reading_plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
        Route::put('/reading-plans/{reading_plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
        Route::post('/reading-plans/{reading_plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');

        // 書計画削除処理
        Route::delete('/reading-plans/{reading_plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');

        // PG14	マイ読書レポート
        Route::get('/reports', [ReadingReportController::class, 'index'])->name('reports.index');

        // PG18 通知一覧
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'update'])->name('notifications.read');
    }
);
