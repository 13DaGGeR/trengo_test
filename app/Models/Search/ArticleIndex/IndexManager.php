<?php

declare(strict_types=1);

namespace App\Models\Search\ArticleIndex;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class IndexManager
{
    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function createArticlesIndex(Client $client): void
    {
        $client->indices()->create([
            'index' => Indexer::INDEX,
            'mappings' => [
                'properties' => [
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'simple',
                    ],
                    'body' => [
                        'type' => 'text',
                        'analyzer' => 'simple',
                    ],
                ]
            ]
        ]);
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function dropArticlesIndex(Client $client): void
    {
        $client->indices()->delete([
            'index' => Indexer::INDEX,
        ]);
    }
}
