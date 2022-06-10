<?php

declare(strict_types=1);

namespace App\Models\Rating;

/**
 * Fair scoring for articles
 *
 * @see https://www.evanmiller.org/how-not-to-sort-by-average-rating.html
 * @see https://en.wikipedia.org/wiki/Binomial_proportion_confidence_interval#Wilson_score_interval
 */
class ArticleScore
{
    public function getScore(float $avg, int $totalVotes): float {
        /**
         * Given:
         * (avg_positive_rate * positive_rates + avg_negative_rate * negative_rates) / total_rates = average_rate
         * and
         * negative_rates + positive_rates = total_rates
         *
         * Lets use 4.5 for avg_positive_rate and 1.5 for avg_negative_rate
         */
        $positive = (int)ceil(($avg * $totalVotes - 1.5 * $totalVotes) / 3);
        $negative = max(0, $totalVotes - $positive);
        return $this->getCILowerBound($positive, $negative);
    }

    private function getCILowerBound(int $positive, int $negative): float {
        if ($positive + $negative === 0) {
            return 0;
        }
        return (
                ($positive + 1.9208)
                / ($positive + $negative)
                - 1.96 * sqrt(($positive * $negative) / ($positive + $negative) + 0.9604)
                / ($positive + $negative)
            ) / (1 + 3.8416 / ($positive + $negative));
    }
}
