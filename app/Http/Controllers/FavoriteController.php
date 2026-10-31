<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧表示
     *
     * @param  Request  $request：HTTPリクエスト
     * @return View：お気に入り一覧画面ビュー
     */
    public function index(Request $request): View
    {
        // ログインユーザーのお気に入り書籍を取得（ページネーション対応）
        $books = $request->user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入りの登録・解除を切り替える（トグル）
     *
     * @param  Request  $request：HTTPリクエスト
     * @return RedirectResponse：直前のページへのリダイレクト
     */
    public function toggle(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        // ログインユーザーのお気に入りリストに対して toggle を実行
        $user->favoriteBooks()->toggle($book);

        return back()->with('success', 'お気に入りを更新しました。');
    }
}
