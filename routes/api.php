<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\BookController;

Route::apiResource('v1/books', BookController::class);
