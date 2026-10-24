<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReadingReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. 基本サマリー
        $totalReviews = Review::where('user_id', $user->id)->count();  //  総レビュー数
        $booksRead = Review::where('user_id', $user->id)->distinct('book_id')->count('book_id');  // 読了冊数
        $averageRating = $totalReviews > 0 ? Review::where('user_id', $user->id)->avg('rating') : 0;  //  平均評価点

        // 2. 評価分布（1〜5の各件数）
        $ratingDistribution = collect([1, 2, 3, 4, 5])->mapWithKeys(function ($star) use ($user) {
            $count = Review::where('user_id', $user->id)->where('rating', $star)->count();

            return [$star - 1 => $count];
        });

        // 3. 高評価書籍 TOP5 (評価4以上)
        $topRatedBooks = Review::where('user_id', $user->id)
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            });

        // 4. ジャンル別評価傾向 TOP5
        $genreRatings = Genre::whereHas('books.reviews', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['books.reviews' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->map(function ($genre) use ($user) {
                $reviews = $genre->books->flatMap->reviews->where('user_id', $user->id);

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => $reviews->count(),
                    'average_rating' => $reviews->avg('rating') ?? 0,
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        // 画面表示ビューを返す処理
        return view('reports.index', compact('stats'));
    }
}
