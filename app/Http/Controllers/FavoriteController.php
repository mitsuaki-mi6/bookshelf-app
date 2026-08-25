<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        // お気に入り一覧画面仮作成（後ほど修正）
        return view('favorites.index');
    }
}
