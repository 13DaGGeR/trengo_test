<?php

declare(strict_types=1);

namespace App\Models\Search\ArticleIndex;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class Searcher
{
    private Client $client;

    public function __construct()
    {
        $this->client = app()->make(Client::class);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function getArticleIds(string $text, int $limit = 1000): array
    {
        $res = $this->client->search([
            'index' => Indexer::INDEX,
            'size' => $limit,
            'stored_fields' => '_id',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $text,
                        'zero_terms_query' => 'all',
                        'fields' => ['body', 'title^1.25'],
                        'fuzziness' => 2,
                        'prefix_length' => 1,
                    ]
                ]
            ]
        ]);
        $result = $res->asArray()['hits']['hits'] ?? [];
        if (count($result) > 0) {
            $result = array_map('intval', array_column($result, '_id'));
        }

        return $result;
    }
}
