<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateArticleTest extends TestCase
{
    use RefreshDatabase;

    public function testHappyPath(): void
    {
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $article = Article::factory(1)->create()->first();
        $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 5
        ])->assertStatus(201);

        $article->refresh();

        $this->assertSame(5., $article->rating);
        $this->assertGreaterThan(0, $article->rating_score);
    }

    public function testConsequentVotes(): void
    {
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $article = Article::factory(1)->create()->first();
        $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 4
        ])->assertStatus(201);

        $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 1
        ])->assertStatus(201);

        $article->refresh();

        $this->assertSame(4., $article->rating);
    }

    public function testWrongArticle(): void
    {
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $response = $this->post('/api/ratings/', [
            'article_id' => PHP_INT_MAX,
            'value' => 4
        ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
    }

    public function testWrongValues(): void
    {
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $article = Article::factory(1)->create()->first();
        $response = $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 0
        ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());

        $response = $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 6
        ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
    }

    public function testConsequentVotesWithDifferentIps(): void
    {
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $article = Article::factory(1)->create()->first();
        $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 5
        ])->assertStatus(201);

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.2'];
        $this->post('/api/ratings/', [
            'article_id' => $article->id,
            'value' => 1
        ])->assertStatus(201);

        $article->refresh();

        $this->assertSame(3., $article->rating);
    }
}
