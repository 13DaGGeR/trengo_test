<?php

declare(strict_types=1);

namespace Articles;

use App\Jobs\CountView;
use App\Models\Article;
use App\Models\Category;
use App\Models\Views\ViewCountManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

    public function testViewCountPostponed(): void
    {
        $article = Article::factory(1)->create()->first();

        Queue::fake(CountView::class);
        $this->get('/api/articles/'.$article->id)
            ->assertStatus(200);

        $article->refresh();
        $this->assertSame(0, $article->getTotalViews(), 'count of views is postponed');

        Queue::assertPushedOn('views', CountView::class, function (CountView $job) use ($article) {
            $this->assertSame($article->id, $job->articleId);
            $job->handle(new ViewCountManager()); # handle to test updates
            return true;
        });

        $article->refresh();
        $this->assertSame(1, $article->getTotalViews());

        $this->get('/api/articles/'.$article->id)
            ->assertStatus(200);
    }
}
