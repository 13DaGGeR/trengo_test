<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ArticleScoreTest extends TestCase
{

    /**
     * @return array<array{0: float, 1: int, 0: float, 1: int}> as avg1, votesCount1, avg2, votesCount2
     */
    public function provideCases(): array {
        return [
            'bigger average rating with same amount of votes' => [
                5, 10, 4.5, 10
            ],
            'one rate is better then none' => [
                1, 1, 0, 0
            ],
            'more votes with same avg rate wins' => [
                4, 100, 4, 99
            ],
            'a hundred "fours" are better than one "five"' => [
                4, 100, 5, 1
            ],
        ];
    }

    /**
     * @dataProvider provideCases
     * @param  float  $avg1
     * @param  int  $count1
     * @param  float  $avg2
     * @param  int  $count2
     * @return void
     */
    public function testFirstScoreIsHigher(float $avg1, int $count1, float $avg2, int $count2): void {
        $scorer = new App\Models\Rating\ArticleScore();
        $this->assertGreaterThan($scorer->getScore($avg2, $count2), $scorer->getScore($avg1, $count1));
    }
}
