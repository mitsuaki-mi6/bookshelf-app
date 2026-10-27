<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexReadingPlanRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'in:in_progress,completed,expired'],
        ];
    }
}
