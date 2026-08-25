<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    // PG01 書籍一覧（トップ）
    public function index()
    {
        $books = Book::with('genres')->paginate(10);
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
        $book->delete();

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
        //  後で修正　ここから
        // バリデーション済みのデータに、一時的に user_id (例: 1) を追加する
        $data = $request->validated();
        $data['user_id'] = 1; // データベースに存在するユーザーIDを指定
        // 後で修正　ここまで

        $genreIds = $data['genres'];
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
        $genres = Genre::all();
        $book->load('genres');

        return view('books.edit', compact('book', 'genres'));
    }

    // 書籍を更新する
    public function update(UpdateBookRequest $request, Book $book)
    {
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
