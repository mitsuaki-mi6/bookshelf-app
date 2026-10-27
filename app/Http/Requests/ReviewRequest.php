<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるかどうかを判定する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * リクエストに適用されるバリデーションルールを取得する
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'in:1,2,3,4,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * 定義済みバリデーションルールのエラーメッセージを取得する
     */
    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください',
            'comment.required' => '評価コメントを入力してください',
            'comment.max' => 'コメントは1000文字以内で入力してください',
        ];
    }
}
