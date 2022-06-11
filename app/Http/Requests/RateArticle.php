<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateArticle extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'article_id' => 'required|exists:articles,id',
            'value' => 'required|numeric|min:1|max:5',
        ];
    }
}
