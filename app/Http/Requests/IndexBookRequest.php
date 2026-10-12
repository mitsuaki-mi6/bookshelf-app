<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // 書籍一覧画面（トップ）バリデーションルール
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'sort' => ['nullable', 'string', 'in:newest,oldest,title,rating'],
        ];
    }

    // バリデーションメッセージ
    public function messages(): array
    {
        return [
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'genre.integer' => 'ジャンルIDは数値で入力してください',
            'genre.exists' => '選択されたジャンルは存在しません',
            'sort.in' => '無効な並び順が指定されています',
        ];
    }
}
