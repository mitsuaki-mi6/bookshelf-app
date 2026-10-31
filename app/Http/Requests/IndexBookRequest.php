<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'sort' => ['nullable', 'string', 'in:newest,oldest,title,rating'],
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
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'genre.integer' => 'ジャンルIDは数値で入力してください',
            'genre.exists' => '選択されたジャンルは存在しません',
            'sort.in' => '無効な並び順が指定されています',
        ];
    }
}
