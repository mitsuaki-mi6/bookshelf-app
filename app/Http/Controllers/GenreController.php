<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        // ジャンル管理画面仮作成。後ほど修正する
        $genres = Genre::all();

        return view('genres.index', compact('genres'));
    }
}
