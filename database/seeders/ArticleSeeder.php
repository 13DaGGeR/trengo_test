<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Search\ArticleIndex\Indexer;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function run()
    {
        $categories = Category::all()->all();
        shuffle($categories);
        $list1 = array_slice($categories, 0, 3);
        $list2 = array_slice($categories, 2, 4);
        $list3 = [$categories[5]];

        Article::factory(500)
            ->hasAttached($list1)
            ->create();
        Article::factory(200)
            ->hasAttached($list2)
            ->create();
        Article::factory(200)
            ->hasAttached($list3)
            ->create();
        Article::factory(100)
            ->create();

        (new Indexer)->reindex();
    }
}
