<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetArticleTest extends TestCase
{
    use RefreshDatabase;

    public function testCorrectArticleData(): void
    {
        $categories = Category::factory(2)->create()->all();
        $article = Article::factory(1)->hasAttached($categories)->create()->first();

        $categoriesAsArray = [];
        foreach ($categories as $category) {
            $categoriesAsArray[] = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        }

        $this->get('/api/articles/'.$article->id)
            ->assertStatus(200)
            ->assertJson([
                'id' => $article->id,
                'title' => $article->title,
                'body' => $article->body,
                'categories' => $categoriesAsArray,
                'rating' => (int)$article->rating,
            ], true);
    }

    public function testNotFound(): void
    {
        Article::factory(1)->create();
        $this->get('/api/articles/'.PHP_INT_MAX)
            ->assertStatus(404);
    }

    public function testViewCountsOnce(): void
    {
        $article = Article::factory(1)->create()->first();

        $this->get('/api/articles/'.$article->id)
            ->assertStatus(200);
        $article->refresh();
        $this->assertSame(1, $article->getTotalViews());

        $this->get('/api/articles/'.$article->id)
            ->assertStatus(200);
        $article->refresh();
        $this->assertSame(1, $article->getTotalViews());
    }
}
