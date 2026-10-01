<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Http\Requests\StoreReviewRequest;  // 投稿用
use App\Models\Book;        // 更新用
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // レビューを登録する
    public function store(StoreReviewRequest $request, Book $book)
    {
        $book->reviews()->create(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id]
        ));

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    // レビュー編集
    // レビュー編集画面を表示する
    public function edit(Book $book, Review $review)
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        return view('reviews.edit', compact('book', 'review'));
    }

    // レビューを更新する
    public function update(ReviewRequest $request, Book $book, Review $review)
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    // レビューを削除する
    public function destroy(Review $review)
    {
        // レビューの所有者がログイン中のユーザーでなければ403エラー
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        // 削除する前に紐づく書籍を変数に保持しておく
        $book = $review->book;

        // レビューを削除
        $review->delete();

        // 保持しておいた $book を使って詳細ページへリダイレクト
        return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました。');
    }

    // レビューのいいねを切り替える（トグル）
    public function like(Request $request, Review $review)
    {
        $user = $request->user();

        // すでに「いいね」しているか判定し、存在すれば削除、なければ追加する
        $user->likedReviews()->toggle($review);

        return back()->with('success', 'いいねを更新しました。');
    }
}
