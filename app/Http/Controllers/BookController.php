<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    // Google Books API を使った ISBN 検索処理
    public function searchIsbn($isbn)
    {
        // 13桁の数字かどうかチェック (13桁以外 -> 400)
        if (!preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁で入力してください。',
            ], 400);
        }
        try {
            $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
                'q' => $isbn,
                'key' => config('services.google.books_api_key'),
            ]);
        } catch (\Exception $e) {
            // API通信エラー -> 500
            return response()->json([
                'error' => 'API通信エラーが発生しました。',
            ], 500);
        }

        // Google Books API のクォータ超過 -> 429
        if ($response->status() === 429) {
            return response()->json([
                'error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。',
            ], 429);
        }

        // その他のAPI通信エラー -> 500
        if ($response->failed()) {
            return response()->json([
                'error' => 'API通信エラーが発生しました。',
            ], 500);
        }

        // 書籍が見つからない -> 404 （ここをスッキリ修正！）
        if (empty($response->json('items'))) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $response->json('items')[0]['volumeInfo'] ?? [];

        return response()->json([
            'title' => $volumeInfo['title'] ?? '',
            'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
            'isbn' => $isbn,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? '',
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
        ], 200);
    }

    // PG01 書籍一覧（トップ）
    public function index(IndexBookRequest $request)
    {
        // バリデーションは IndexBookRequest で実行済み
        $request->validated();

        $query = Book::query();

        // キーワード検索（書籍名、著者の部分一致）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('title COLLATE utf8mb4_bin LIKE ?', ['%' . $keyword . '%'])
                    ->orWhereRaw('author COLLATE utf8mb4_bin LIKE ?', ['%' . $keyword . '%']);
            });
        }

        // ジャンル検索（多対多のリレーションを考慮）
        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // ソート処理
        $sort = $request->input('sort', 'latest'); // デフォルトは latest

        // 評価順ソートのために平均評価をあらかじめロードしておく
        $query->withAvg('reviews', 'rating');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                // 評価が高い順
                $query->orderByDesc('reviews_avg_rating');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $books = $query->with('genres')->paginate(10)->withQueryString();
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    // PG02	書籍詳細
    // 書籍詳細画面を表示する
    public function show(Book $book)
    {
        // 詳細画面で必要なリレーションや平均評価もビューに渡す
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);
        $book->loadAvg('reviews', 'rating');

        return view('books.show', compact('book'));
    }

    // 書籍を削除する
    public function destroy(Book $book)
    {
        // 書籍の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $book->genres()->detach(); // 中間テーブルの削除
        $book->favorites()->delete(); // お気に入りの削除

        // レビューとそのいいねを削除する場合
        foreach ($book->reviews as $review) {
            $review->reviewLikes()->delete(); // いいねの削除
        }
        $book->reviews()->delete(); // レビューの削除

        $book->delete();  // 書籍を論理削除

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }

    // PG03	書籍登録
    // 書籍登録画面を表示する
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    // 書籍を登録する
    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        // ログイン中のユーザーIDを動的に設定する
        $data['user_id'] = Auth::id();

        $genreIds = $data['genres'] ?? []; // ジャンルが未選択の場合の安全対策
        unset($data['genres']); // booksテーブルに存在しないカラムなので削除

        // 書籍テーブルに登録
        $book = Book::create($data);
        // 中間テーブルにジャンルを紐付け
        $book->genres()->sync($genreIds);

        return redirect()->route('books.index')->with('success', '書籍を登録しました。');
    }

    // PG04 書籍編集
    // 書籍編集画面を表示する
    public function edit(Book $book)
    {
        // 書籍の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $genres = Genre::all();
        $book->load('genres');

        return view('books.edit', compact('book', 'genres'));
    }

    // 書籍を更新する
    public function update(UpdateBookRequest $request, Book $book)
    {
        // 書籍の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        // バリデーション済みのデータを取得し、書籍情報を更新
        $book->update($request->validated());

        // ジャンルの中間テーブル情報を更新（チェックされたジャンルIDで同期）
        if ($request->has('genres')) {
            $book->genres()->sync($request->genres);
        } else {
            // 万が一ジャンルがすべて外された場合の考慮（必要に応じて）
            $book->genres()->sync([]);
        }

        return redirect()->route('books.index')->with('success', '書籍を更新しました。');
    }
}

