<?php

declare(strict_types=1);

namespace App\Models\Score;

/**
 * Fair scoring for articles
 */
class ArticleScore
{
    public function getScore(float $avg, int $totalVotes): float {
        return .0;
    }
}
