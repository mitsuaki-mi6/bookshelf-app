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

// PG02 書籍詳細 ＆ レビュー投稿
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// PG03 書籍登録
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');

// PG04 書籍編集
Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

// PG05 ジャンル一覧
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

// PG10 お気に入り一覧
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

// PG11 ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// 以下は仮のコード。後で修正必須
// 認証系ルートのプレースホルダー（エラー回避用）
Route::get('/login', function () {
    return 'ログイン画面（未実装）';
})->name('login');

Route::post('/logout', function () {
    // ログアウト処理のスタブ
    return redirect('/');
})->name('logout');

Route::get('/register', function () {
    return '会員登録画面（未実装）';
})->name('register');
