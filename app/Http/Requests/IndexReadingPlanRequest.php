<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // 読書計画一覧バリデーションルール
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:in_progress,completed,expired'],
        ];
    }

    // バリデーションメッセージ  無し
}
