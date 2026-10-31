<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるかどうかを判定する
     *
     * @return bool：権限の有無
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * リクエストに適用されるバリデーションルールを取得する
     *
     * @return array<string, mixed>：バリデーションルール
     */
    public function rules(): array
    {
        return [
            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'digits:13', 'unique:books,isbn,'.$this->route('book')],
            'published_date' => ['nullable', 'date', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * 定義済みバリデーションルールのエラーメッセージを取得する
     *
     * @return array<string, string>：エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'title.required' => '書籍名を入力してください',
            'title.max' => '書籍名は255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者名は255文字以内で入力してください',
            'isbn.digits' => 'ISBNコードは数字13文字で入力してください',
            'isbn.unique' => 'そのISBNコードは既に使用されています',
            'published_date.date' => '出版日は有効な日付形式で入力してください',
            'published_date.max' => '出版日は有効な日付形式で入力してください',
            'description.max' => '説明は255文字以内で入力してください',
            'image_url.url' => '画像URLは有効なURL形式で入力してください',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルは１つ以上選択してください',
            'user_id.required' => '登録者IDを入力してください',
            'user_id.integer' => '登録者IDは数値で入力してください',
            'user_id.exists' => '指定された登録者IDは存在しません',
        ];
    }
}
