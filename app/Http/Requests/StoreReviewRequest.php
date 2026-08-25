<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    //  レビュー投稿　バリデーションルール
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'in:1,2,3,4,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    // バリデーションメッセージ
    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください',
            'comment.required' => '評価コメントを入力してください',
            'comment.max' => 'コメントは1000文字以内で入力してください',
        ];
    }
}
