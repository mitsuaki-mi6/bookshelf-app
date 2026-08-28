<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;

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

// PG05 ジャンル一覧
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

// PG11 ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ----------------------------------------------------------------
// ログインユーザーのみアクセスできるルート（認証必須）
// ----------------------------------------------------------------
Route::middleware('auth')->group(
    function () {
        // PG03 書籍登録
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');

        // PG04 書籍編集・削除
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');


        // PG02 書籍詳細 ＆ レビュー投稿
        // お気に入り切り替え
        Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        // レビュー投稿
        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        // レビュー関連（いいね）
        Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

        // レビュー関連（編集）
        Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

        // レビュー関連（削除）
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // PG10 お気に入り一覧
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    }
);
