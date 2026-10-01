<?php

use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Support\Facades\Route;

Route::apiResource('v1/books', BookController::class);
