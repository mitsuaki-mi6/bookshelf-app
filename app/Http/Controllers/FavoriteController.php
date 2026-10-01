<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // お気に入り一覧表示
    public function index(Request $request)
    {
        // ログインユーザーのお気に入り書籍を取得（ページネーション対応）
        $books = $request->user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    // お気に入りの登録・解除を切り替える（トグル）
    public function toggle(Request $request, Book $book)
    {
        $user = $request->user();

        // ログインユーザーのお気に入りリストに対して toggle を実行
        $user->favoriteBooks()->toggle($book);

        return back()->with('success', 'お気に入りを更新しました。');
    }
}
