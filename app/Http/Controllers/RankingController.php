<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        // レビュー平均が高い順に上位10件の書籍を取得
        $rankingBooks = Book::with('genres')->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating')->take(10)->get();

        return view('ranking.index', compact('rankingBooks'));
    }
}
