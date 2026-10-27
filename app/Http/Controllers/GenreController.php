<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * PG05 ジャンル一覧
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * PG06 ジャンル詳細
     */
    public function show(int $id): View
    {
        $genre = Genre::findOrFail($id);
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル登録画面表示
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * PG07 ジャンル登録
     */
    public function store(GenreRequest $request): RedirectResponse
    {
        $request->validated();
        Genre::create($request->all());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    /**
     * PG07 ジャンル編集
     */
    public function edit(int $id): View
    {
        $genre = Genre::findOrFail($id);

        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンル情報更新
     */
    public function update(UpdateGenreRequest $request, int $id): RedirectResponse
    {
        $request->validated();
        $genre = Genre::findOrFail($id);
        $genre->update($request->all());

        return redirect()->route('genres.index', $genre)->with('success', 'ジャンル情報を更新しました。');
    }

    /**
     * ジャンル削除処理
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        // 書籍が紐づいている場合は削除を制限する
        if ($genre->books()->count() > 0) {
            return redirect()->route('genres.index')->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
