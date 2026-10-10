<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // 読書計画作成バリデーションルール
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'string', 'in:進行中,完了,期限切れ'],
        ];
    }

    // バリデーションメッセージ
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.integer' => '書籍IDは数値で入力してください',
            'book_id.exists' => '選択された書籍は存在しません',
            'target_date.required' => '目標日を入力してください',
            'target_date.date' => '目標日は有効な日付形式で入力してください',
            'target_date.after_or_equal' => '目標日は今日以降の日付を入力してください',
            'status.required' => 'ステータスを入力してください',
            'status.in' => '無効なステータスが指定されています',
        ];
    }
}
