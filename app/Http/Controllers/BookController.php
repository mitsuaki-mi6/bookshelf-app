<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    // PG01 書籍一覧（トップ）
    public function index()
    {
        $books = Book::with('genres')->paginate(10);
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }
}
