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
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    // バリデーションメッセージ
    public function messages(): array
    {
        return [
            'target_date.required' => '目標日を入力してください',
            'target_date.date' => '目標日は有効な日付形式で入力してください',
            'target_date.after_or_equal' => '目標日は今日以降の日付を入力してください',
        ];
    }
}
