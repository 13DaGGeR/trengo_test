<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ArticlesList\ArticleListRequest;
use App\Models\ArticlesList\SortOrder;
use Illuminate\Foundation\Http\FormRequest;

class GetArticles extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => 'string|nullable|max:255',
            'categories' => 'array|exists:categories,id',
            'date_from' => 'date|nullable',
            'date_to' => 'date|nullable' . ($this->has('date_from') ? '|gte:date_from' : ''),
            'sort' => 'in:' . SortOrder::VIEWS . ',' . SortOrder::RATING,
            'trending_date' => 'required_if:sort,views',
            'page' => 'numeric|min:' . ArticleListRequest::MIN_PAGE . '|max:' . ArticleListRequest::MAX_PAGE,
            'page_size' => 'numeric|min:' . ArticleListRequest::MIN_PAGE_SIZE . '|max:' . ArticleListRequest::MAX_PAGE_SIZE,
        ];
    }

    protected function prepareForValidation()
    {
        if (is_string($this->categories)) {
            $this->merge([
                'categories' => explode(',', $this->categories),
            ]);
        }
    }
}
