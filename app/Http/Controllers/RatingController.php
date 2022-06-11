<?php

namespace App\Http\Controllers;

use App\Http\Requests\RateArticle;
use App\Models\Rating\RatingManager;


class RatingController extends Controller
{
    public function store(RateArticle $request)
    {
        (new RatingManager())->rate(
            (int)$request->validated('article_id'),
            (int)$request->validated('value'),
            $request->ip(),
        );
        return response('', 201);
    }
}
