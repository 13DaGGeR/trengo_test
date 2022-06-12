<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ElasticsearchProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function($app) {
            $host = config('database.elasticsearch.host').':'.config('database.elasticsearch.port');
            return ClientBuilder::create()
                ->setHosts([$host])
                ->build();
        });
    }

    public function provides(): array
    {
        return [Client::class];
    }
}
