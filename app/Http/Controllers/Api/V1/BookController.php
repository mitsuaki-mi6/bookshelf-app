<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Favorite;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    // 書籍一覧を取得する(GET /api/v1/books)
    public function index(IndexBookRequest $request)
    {
        // クエリのベースを作成しリレーションを事前ロード
        $query = Book::with(['genres', 'reviews']);

        // キーワード検索（タイトル または 著者名の部分一致）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンルIDによる絞り込み（中間テーブルを介したリレーション検索）
        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // ページネーションの取得（デフォルト10件、リクエストがあれば指定件数）
        $perPage = $request->input('per_page', 10);
        $books = $query->paginate($perPage);

        // APIリソースを使って整形して返却(リスト)
        return BookResource::collection($books);
    }

    // 書籍詳細を取得する(GET /api/v1/books/{book})
    public function show($id)
    {
        // 書籍詳細で必要なリレーションを事前ロード
        $book = Book::with(['genres', 'reviews.user'])->findOrFail($id);

        // APIリソースを使って単体データを整形して返却(1件)
        return new BookResource($book);
    }


    // 書籍を新規登録する(POST /api/v1/books)
    public function store(StoreBookRequest $request)
    {
        // バリデーション済みのデータを取得
        $data = $request->validated();

        $genreIds = $data['genres'] ?? []; // ジャンルが未選択の場合の安全対策
        unset($data['genres']); // booksテーブルに存在しないカラムなので削除

        // 書籍テーブルに登録
        $book = Book::create($data);
        // 中間テーブルにジャンルを紐付け
        $book->genres()->sync($genreIds);

        // ジャンルをロードし、ステータスコード 201でレスポンスを返却
        return response()->json([
            'message' => '書籍が正常に登録されました。',
            'data' => new BookResource($book->load('genres')),
        ], 201);
    }

    // 書籍を更新する(PUT /api/v1/books/{book})
    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::findOrFail($id);

        // バリデーション済みのデータを変数に受ける
        $data = $request->validated();

        // genres がリクエストに含まれている場合のみ処理する
        if (array_key_exists('genres', $data)) {
            $genreIds = $data['genres'];
            unset($data['genres']); // booksテーブルに存在しないカラムなので除外
            $book->genres()->sync($genreIds);
        }

        // 書籍情報を更新
        $book->update($data);

        // ジャンルをロードし、APIリソースを使って単体データを整形して返却
        return new BookResource($book->load('genres'));
    }

    // 書籍を削除する (DELETE /api/v1/books/{book})
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // 中間テーブル（book_genre）の削除
        $book->genres()->detach();

        // お気に入り（favorites）の削除
        $book->favorites()->delete();

        // レビューに紐づくいいね（review_likes）を削除してからレビューを削除
        foreach ($book->reviews as $review) {
            $review->reviewLikes()->delete();
        }
        $book->reviews()->delete();

        // 書籍本体を削除
        $book->delete();

        // 削除成功時にメッセージをJSONで返却（ステータスコード200）
        return response()->json([
            'message' => '書籍が正常に削除されました。',
        ], 200);
    }
}
