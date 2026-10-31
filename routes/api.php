<?php

use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Support\Facades\Route;

// 認証なしでアクセスできる公開ルート（一覧・詳細）
Route::apiResource('v1/books', BookController::class)->only(['index', 'show']);

// Sanctum認証が必須のルート（登録・更新・削除）
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/books', BookController::class)->except(['index', 'show']);
});
