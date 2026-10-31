<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    /**
     * レビュー平均が高い順に上位10件の書籍を取得
     *
     * @return View：ランキング一覧画面ビュー
     */
    public function index(): View
    {
        $rankedBooks = Book::with('genres')->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->has('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
