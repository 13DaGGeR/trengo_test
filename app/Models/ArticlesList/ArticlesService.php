<?php

declare(strict_types=1);

namespace App\Models\ArticlesList;

use App\Models\Article;
use App\Models\Search\ArticleIndex\Searcher;
use Illuminate\Database\Eloquent\Builder;

class ArticlesService
{
    public function getList(ArticleListRequest $request): array
    {
        return $this->getQueryBuilder($request)
            ->with('categories')
            ->offset($request->getPageSize() * ($request->getPage() - 1))
            ->limit($request->getPageSize())
            ->get()
            ->all();
    }

    public function getCount(ArticleListRequest $request): int
    {
        return $this->getQueryBuilder($request)->count();
    }

    private function getQueryBuilder(ArticleListRequest $request): Builder
    {
        $builder = Article::query();
        if (count($request->getCategories()) > 0) {
            $builder->whereHas('categories', static function (Builder $q) use ($request) {
                $q->whereIn('id', $request->getCategories());
            });
        }
        if ($request->getDateFrom() !== null) {
            $builder->where('created_at', '>=', $request->getDateFrom());
        }
        if ($request->getDateTo() !== null) {
            $builder->where('created_at', '<=', $request->getDateTo());
        }
        if ($request->getQuery() !== '') {
            $ids = (new Searcher())->getArticleIds($request->getQuery());
            $builder->whereIn('id', $ids);
        }
        if ($request->getSortOrder() === SortOrder::VIEWS) {
            $builder
                ->select('articles.*')
                ->leftJoin('article_views AS av', static function ($join) use ($request) {
                    $join->on('articles.id', '=', 'av.article_id')
                        ->where('av.date', '>=', $request->getTrendingDate());
                })
                ->groupBy('articles.id')
                ->orderByRaw('SUM(av.count) DESC');
        } else {
            $builder->orderBy('rating_score', 'DESC');
        }

        return $builder;
    }
}
