<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArticle;
use App\Jobs\CountView;
use App\Models\Article;
use App\Models\Category;
use App\Models\Views\ViewCountManager;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show(Request $request, int $id)
    {
        $article = Article::query()->with('categories')->findOrFail($id);
        $ip = (string)$request->ip();

        CountView::dispatch($id, $ip, now()->timestamp);
        return $article;
    }

    public function store(CreateArticle $request)
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
}
