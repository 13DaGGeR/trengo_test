<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @throws \JsonException */
    public function testHappyPath()
    {
        $text = 'Nam libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.';
        $response = $this->post('/api/articles', [
            'title' => 'test',
            'body' => $text
        ])->assertStatus(201)
            ->assertSimilarJson([
                'id' => 0
            ]);
        $id = (int)json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR)->id;

        $this->assertGreaterThan(0, $id);
        $article = Article::query()->find($id)->first();
        $this->assertSame('test', $article->title);
        $this->assertSame($text, $article->body);
        $this->assertSame(.0, $article->rating);
        $this->assertSame(.0, $article->rating_score);
        $this->assertSame(.0, $article->getTotalViews());
    }
}
