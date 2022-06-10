<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Rating\Rating;
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
        $limit = 10000;
        do {
            $article = Article::all()->random(1)->first();
            /** @noinspection PhpUnhandledExceptionInspection */
            $number = min(random_int(1, 1000), $limit);
            Rating::factory($number)
                ->for($article)
                ->create();
            $limit -= $number;
        } while ($limit > 0);
    }
}
