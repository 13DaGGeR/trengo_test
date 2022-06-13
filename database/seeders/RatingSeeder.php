<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Rating\Rating;
use App\Models\Rating\RatingManager;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ratingManager = new RatingManager();
        $limit = 10000;
        do {
            $article = Article::all()->random(1)->first();
            /** @noinspection PhpUnhandledExceptionInspection */
            $number = min(random_int(10, 1000), $limit);
            Rating::factory($number)
                ->for($article)
                ->create();
            $ratingManager->refreshArticleRating($article);
            $limit -= $number;
        } while ($limit > 0);
    }
}
