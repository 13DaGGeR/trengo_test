<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListArticlesRatingSortingTest extends TestCase
{
    use RefreshDatabase;

    public function testRatingSort(): void
    {
        [$noRates, $highAmountOfGoodRates, $smallAmountOfHighRates] = Article::factory(3)->create()->all();

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $this->post('/api/ratings/', [
            'article_id' => $highAmountOfGoodRates->id,
            'value' => 5
        ])->assertStatus(201);
        $this->post('/api/ratings/', [
            'article_id' => $smallAmountOfHighRates->id,
            'value' => 5
        ])->assertStatus(201);

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.2'];
        $this->post('/api/ratings/', [
            'article_id' => $highAmountOfGoodRates->id,
            'value' => 4
        ])->assertStatus(201);

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.3'];
        $this->post('/api/ratings/', [
            'article_id' => $highAmountOfGoodRates->id,
            'value' => 5
        ])->assertStatus(201);
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.4'];
        $this->post('/api/ratings/', [
            'article_id' => $highAmountOfGoodRates->id,
            'value' => 5
        ])->assertStatus(201);
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.5'];
        $this->post('/api/ratings/', [
            'article_id' => $highAmountOfGoodRates->id,
            'value' => 5
        ])->assertStatus(201);

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'sort' => 'rating',
            ])
        )
            ->assertStatus(200);

        $highAmountOfGoodRates->refresh();
        $smallAmountOfHighRates->refresh();
        $noRates->refresh();

        $this->assertSame(4.8, $highAmountOfGoodRates->rating);
        $this->assertSame(5., $smallAmountOfHighRates->rating);
        $this->assertSame(0., $noRates->rating);

        $json = $response->json();
        $this->assertCount(3, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertSame($highAmountOfGoodRates->id, $ids[0]);
        $this->assertSame($smallAmountOfHighRates->id, $ids[1]);
        $this->assertSame($noRates->id, $ids[2]);
    }
}
