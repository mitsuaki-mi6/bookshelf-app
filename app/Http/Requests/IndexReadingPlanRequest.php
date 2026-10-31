<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexReadingPlanRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'in:in_progress,completed,expired'],
        ];
    }
}
