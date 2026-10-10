<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // 書籍更新バリデーションルール
    public function rules(): array
    {
        return [
            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'digits:13', 'unique:books,isbn,' . $this->route('book')->id], // required → nullableに変更　2026/10/10 MN
            'published_date' => ['nullable', 'date', 'max:10'], // required → nullableに変更　2026/10/10 MN
            'description' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    // バリデーションメッセージ
    public function messages(): array
    {
        return [
            'title.required' => '書籍名を入力してください',
            'title.max' => '書籍名は255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者名は255文字以内で入力してください',
            // 'isbn.required' => 'ISBNコードを入力してください',  削除  2026/10/10 MN
            'isbn.digits' => 'ISBNコードは数字13文字で入力してください',
            'isbn.unique' => 'そのISBNコードは既に使用されています',
            // 'published_date.required' => '出版日を入力してください',  削除  2026/10/10 MN
            'published_date.date' => '出版日は有効な日付形式で入力してください',
            'published_date.max' => '出版日は有効な日付形式で入力してください',
            'description.max' => '説明は255文字以内で入力してください',
            'image_url.url' => '画像URLは有効なURL形式で入力してください',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルは１つ以上選択してください',
        ];
    }
}
