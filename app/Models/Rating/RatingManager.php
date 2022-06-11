<?php

declare(strict_types=1);

namespace App\Models\Rating;

use App\Models\Article;

class RatingManager
{
    public function rate(int $articleId, int $value, string $ip): void
    {
        $isNew = Rating::insertOrIgnore([
                'article_id' => $articleId,
                'ip_address' => $ip,
                'value' => $value,
            ]) > 0;
        if ($isNew) {
            $article = Article::find($articleId);
            $totals = $article->ratings()
                ->selectRaw('COUNT(*) AS `count`')
                ->selectRaw('AVG(value) AS avg')
                ->first();

            $scorer = new ArticleScore();
            $article->rating = (float)$totals->avg;
            $article->rating_score = $scorer->getScore((float)$totals->avg, (int)$totals->count);
            $article->save();
        }
    }
}
