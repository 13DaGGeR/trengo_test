<?php

namespace App\Http\Controllers;

use App\Jobs\CountView;
use App\Models\Article;
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
}
