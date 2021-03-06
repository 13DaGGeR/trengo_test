<?php

declare(strict_types=1);

namespace App\Models\Search\ArticleIndex;

use App\Models\Article;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class Indexer
{
    public const INDEX = 'articles';

    private Client $client;

    public function __construct()
    {
        $this->client = app()->make(Client::class);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function indexArticle(Article $article): void
    {
        $this->indexArticlesBatch([$article]);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function clearIndex(): void
    {
        $this->client->deleteByQuery([
            'index' => self::INDEX,
            'refresh' => true,
            'body' => [
                'query' => [
                    'match_all' => (object)[]
                ]
            ]
        ]);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function reindex(): void
    {
        $this->clearIndex();
        Article::chunk(1000, function (iterable $articles) {
            $this->indexArticlesBatch($articles);
        });
    }

    /**
     * @param  iterable<Article>  $array
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function indexArticlesBatch(iterable $array): void
    {
        $batch = [];
        foreach ($array as $article) {
            $batch[] = [
                'index' => [
                    '_index' => self::INDEX,
                    '_id' => $article->id,
                ]
            ];
            $batch[] = [
                'title' => $article->title,
                'body' => $article->body,
            ];
        }

        if (count($batch) > 0) {
            $this->client->bulk([
                'body' => $batch
            ]);
        }
    }
}
