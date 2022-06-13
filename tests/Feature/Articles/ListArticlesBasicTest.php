<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListArticlesBasicTest extends TestCase
{
    use RefreshDatabase;

    public function testHappyPath()
    {
        $this->get('/api/articles')
            ->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'page',
                'page_size',
                'total',
            ]);
    }

    public function testPager()
    {
        Article::factory(3)->create();
        $response = $this->get(
            '/api/articles?'.http_build_query([
                'page_size' => 2,
                'page' => 1,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(2, $json['items']);

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'page_size' => 2,
                'page' => 2,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);

        $this->get(
            '/api/articles?'.http_build_query([
                'page_size' => 2,
                'page' => 3,
            ])
        )
            ->assertStatus(404);
    }
}
