<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;
use App\Http\Requests\GenreRequest;
use App\Http\Requests\UpdateGenreRequest;

class GenreController extends Controller
{
    // PG05	ジャンル一覧
    public function index()
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    // PG06	ジャンル詳細
    public function show($id)
    {
        $genre = Genre::findOrFail($id);
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    // ジャンル登録
    // ジャンル登録画面表示
    public function create()
    {
        return view('genres.create');
    }
    // PG07	ジャンル登録
    public function store(GenreRequest $request)
    {
        $request->validated();
        Genre::create($request->all());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    // PG07	ジャンル編集
    public function edit($id)
    {
        $genre = Genre::findOrFail($id);
        return view('genres.edit', compact('genre'));
    }

    public function update(UpdateGenreRequest $request, $id)
    {
        $request->validated();
        $genre = Genre::findOrFail($id);
        $genre->update($request->all());

        return redirect()->route('genres.index', $genre)->with('success', 'ジャンル情報を更新しました。');
    }

    // ジャンル削除処理
    public function destroy(Genre $genre)
    {
        // 書籍が紐づいている場合は削除を制限する
        if ($genre->books()->count() > 0) {
            return redirect()->route('genres.index')->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
