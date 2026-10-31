<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを登録する
     *
     * @param  StoreReviewRequest  $request：レビュー登録用バリデーション済みリクエスト
     * @param  Book  $book：レビュー対象の書籍モデル
     * @return RedirectResponse：書籍詳細画面へのリダイレクト
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        // トランザクションで安全に登録
        DB::transaction(function () use ($request, $book) {
            $book->reviews()->create(array_merge(
                $request->validated(),
                ['user_id' => $request->user()->id]
            ));
        });

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集画面を表示する
     *
     * @param  Book  $book：対象の書籍モデル
     * @param  Review  $review：編集対象のレビューモデル
     * @return View：レビュー編集画面ビュー
     */
    public function edit(Book $book, Review $review): View
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        return view('reviews.edit', compact('book', 'review'));
    }

    /**
     * レビューを更新する
     *
     * @param  ReviewRequest  $request：レビュー更新用バリデーション済みリクエスト
     * @param  Book  $book：対象の書籍モデル
     * @param  Review  $review：更新対象のレビューモデル
     * @return RedirectResponse：書籍詳細画面へのリダイレクト
     */
    public function update(ReviewRequest $request, Book $book, Review $review): RedirectResponse
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        // トランザクションで安全に更新
        DB::transaction(function () use ($request, $review) {
            $review->update($request->validated());
        });

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     *
     * @param  Review  $review：削除対象のレビューモデル
     * @return RedirectResponse：書籍詳細画面へのリダイレクト
     */
    public function destroy(Review $review): RedirectResponse
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        // 削除する前に紐づく書籍を変数に保持しておく
        $book = $review->book;

        // トランザクションで安全に削除
        DB::transaction(function () use ($review) {
            // レビューを削除
            $review->delete();
        });

        // 保持しておいた $book を使って詳細ページへリダイレクト
        return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました。');
    }

    /**
     * レビューのいいねを切り替える（トグル）
     *
     * @param  Request  $request：HTTPリクエスト
     * @param  Review  $review：いいね対象のレビューモデル
     * @return RedirectResponse：直前のページへのリダイレクト
     */
    public function like(Request $request, Review $review): RedirectResponse
    {
        $user = $request->user();

        // トランザクションで安全にいいねを切り替え
        DB::transaction(function () use ($user, $review) {
            // すでに「いいね」しているか判定し、存在すれば削除、なければ追加する
            $user->likedReviews()->toggle($review);
        });

        return back()->with('success', 'いいねを更新しました。');
    }
}
