<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArticle;
use App\Http\Requests\GetArticles;
use App\Jobs\CountView;
use App\Models\Article;
use App\Models\ArticlesList\ArticleListRequest;
use App\Models\ArticlesList\ArticlesService;
use App\Models\Category;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    private const MAX_PER_PAGE_FOR_LIST = 100;

    public function show(Request $request, int $id)
    {
        $article = Article::query()->with('categories')->findOrFail($id);
        $ip = (string)$request->ip();

        CountView::dispatch($id, $ip, now()->timestamp);
        return $article;
    }

    public function store(CreateArticle $request): JsonResponse
    {
        /** @var Article $article */
        $article = Article::create($request->validated());
        $categoryIds = $request->validated('categories');
        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categories = Category::query()->find($categoryIds)->all();
            $article->categories()->saveMany($categories);
        }

        return response()->json([
            'id' => $article->id
        ], 201);
    }

    /**
     * @throws Exception
     */
    public function index(GetArticles $request): JsonResponse
    {
        $listRequest = new ArticleListRequest();
        if ($request->has('page_size')) {
            $listRequest->setPageSize($request->page_size);
        }
        if ($request->has('page')) {
            $listRequest->setPage($request->page);
        }
        if ($request->has('date_from')) {
            $listRequest->setDateFrom(new DateTimeImmutable($request->date_from));
        }
        if ($request->has('date_to')) {
            $listRequest->setDateTo(new DateTimeImmutable($request->date_to));
        }
        if ($request->has('categories')) {
            $listRequest->setCategories($request->categories);
        }
        if ($request->has('q')) {
            $listRequest->setQuery($request->q);
        }
        if ($request->has('sort')) {
            $listRequest->setSortOrder($request->sort);
        }
        if ($request->has('trending_date')) {
            $listRequest->setTrendingDate(new DateTimeImmutable($request->trending_date));
        }

        $service = new ArticlesService();
        $count = $service->getCount($listRequest);
        if ($count < ($listRequest->getPage() - 1) * $listRequest->getPageSize()) {
            $code = 404;
            $list = [];
        } else {
            $code = 200;
            $list = $service->getList($listRequest);
        }
        return response()->json([
            'items' => $list,
            'page' => 1,
            'per_page' => self::MAX_PER_PAGE_FOR_LIST,
            'total' => $count,
        ], $code);
    }
}
