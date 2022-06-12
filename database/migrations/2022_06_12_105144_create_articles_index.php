<?php

use App\Models\Search\ArticleIndex\Indexer;
use App\Models\Search\ArticleIndex\IndexManager;
use Elastic\Elasticsearch\Client;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (app()->runningUnitTests()) {
            return;
        }
        (new IndexManager())->createArticlesIndex(app()->make(Client::class));
        (new Indexer())->reindex();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (app()->runningUnitTests()) {
            return;
        }

        (new IndexManager())->dropArticlesIndex(app()->make(Client::class));
    }
};
