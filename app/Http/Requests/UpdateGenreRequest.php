<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGenreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:genres,name,'.$this->route('genre')],
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
            'name.required' => 'ジャンル名を入力してください',
            'name.max' => 'ジャンル名は255文字以内で入力してください',
            'name.unique' => 'そのジャンル名は既に使用されています',
        ];
    }
}
