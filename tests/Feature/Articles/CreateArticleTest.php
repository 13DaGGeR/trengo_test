<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Type\Integer;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @throws \JsonException */
    public function testHappyPath(): void
    {
        $text = 'Nam libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.';
        $response = $this->post('/api/articles', [
            'title' => 'test',
            'body' => $text
        ])->assertStatus(201)
            ->assertJsonStructure([
                'id'
            ]);
        $id = (int)json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR)->id;

        $this->assertGreaterThan(0, $id);
        $article = Article::query()->find($id)->first();
        $this->assertSame('test', $article->title);
        $this->assertSame($text, $article->body);
        $this->assertSame(.0, $article->rating);
        $this->assertSame(.0, $article->rating_score);
        $this->assertSame(0, $article->getTotalViews());
    }

    public function testNoTitle(): void
    {
        $text = 'Nam libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.';
        $response = $this->withExceptionHandling()
            ->post('/api/articles', [
                'body' => $text
            ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());

        $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 't',
                'body' => $text
            ])
            ->assertStatus(201);
    }

    public function testNoBody(): void
    {
        $response = $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 'test',
            ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());

        $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 'test',
                'body' => 't'
            ])
            ->assertStatus(201);
    }

    public function testLongTitle(): void
    {
        $text = 'Nam libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.';
        $response = $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => str_repeat('t', 256),
                'body' => $text
            ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());

        $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => str_repeat('t', 255),
                'body' => $text
            ])
            ->assertStatus(201);
    }

    public function testLongText(): void
    {
        $response = $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 'test',
                'body' => str_repeat('t', 10001)
            ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());

        $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 'test',
                'body' => str_repeat('t', 10000)
            ])
            ->assertStatus(201);
    }

    public function testCreateWithCategories(): void
    {
        $categories = Category::factory(2)->create()->all();
        $ids = array_column($categories, 'id');
        $response = $this->post('/api/articles', [
            'title' => 'test',
            'body' => 'text',
            'categories' => $ids,
        ])->assertStatus(201)
            ->assertJsonStructure([
                'id'
            ]);

        $id = (int)json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR)->id;

        $this->assertGreaterThan(0, $id);
        $article = Article::query()->find($id)->first();

        $realIds = [];
        foreach ($article->categories as $category) {
            $realIds[] = $category->id;
        }
        $this->assertSame($ids, $realIds);
    }

    public function testCreateWithUnknownCategory(): void
    {
        $categories = Category::factory(2)->create()->all();
        $ids = array_column($categories, 'id');
        $ids[] = PHP_INT_MAX;

        $response = $this->withExceptionHandling()
            ->post('/api/articles', [
                'title' => 'test',
                'body' => 'text',
                'categories' => $ids,
            ]);
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
    }

    public function testHtmlCleared(): void
    {
        $response = $this->post('/api/articles', [
            'title' => '<a href="//google.com">test</a>',
            'body' => '<a href="//google.com">text</a>'
        ])->assertStatus(201)
            ->assertJsonStructure([
                'id'
            ]);
        $id = (int)json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR)->id;

        $this->assertGreaterThan(0, $id);
        $article = Article::query()->find($id)->first();
        $this->assertSame('test', $article->title);
        $this->assertSame('text', $article->body);
    }
}
