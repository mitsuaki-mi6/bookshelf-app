<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// 書籍関係
// PG01 書籍一覧（トップ）
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books', [BookController::class, 'index'])->name('books.index');

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

        // PG14	マイ読書レポート（ダミー）
        Route::get('/reports', function () {
            return 'マイ読書レポート（準備中）';
        })->name('reports.index');

        // PG15 読書計画一覧（ダミー）
        Route::get('/reading-plans', function () {
            return '読書計画一覧（準備中）';
        })->name('reading-plans.index');

        // PG18 通知一覧（ダミー）
        Route::get('/notifications', function () {
            return '通知一覧（準備中）';
        })->name('notifications.index');
    }
);
