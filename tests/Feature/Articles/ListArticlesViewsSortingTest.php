<?php

declare(strict_types=1);

namespace Articles;

use App\Jobs\CountView;
use App\Models\Article;
use App\Models\Views\ViewCountManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ListArticlesViewsSortingTest extends TestCase
{
    use RefreshDatabase;

    public function testViewsSort()
    {
        $today = now()->format('Y-m-d');
        [, $popularToday, $popularYesterday] = Article::factory(10)->create()->all();

        Queue::fake(CountView::class);

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.1'];
        $this->get('/api/articles/'.$popularToday->id)->assertStatus(200);
        $this->processQueue();
        $this->get('/api/articles/'.$popularYesterday->id)->assertStatus(200);
        $this->processQueue();
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.2'];
        $this->get('/api/articles/'.$popularToday->id)->assertStatus(200);
        $this->processQueue();

        $this->travel(-1)->days();
        $yesterday = now()->format('Y-m-d');

        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.3'];
        $this->get('/api/articles/'.$popularYesterday->id)->assertStatus(200);
        $this->processQueue();
        $this->serverVariables = ['REMOTE_ADDR' => '1.1.1.4'];
        $this->get('/api/articles/'.$popularYesterday->id)->assertStatus(200);
        $this->processQueue();

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'sort' => 'views',
                'trending_date' => $today,
                'page_size' => 1,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($popularToday->id, $ids);

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'sort' => 'views',
                'trending_date' => $yesterday,
                'page_size' => 1,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($popularYesterday->id, $ids);

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'sort' => 'views',
                'page_size' => 1,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($popularYesterday->id, $ids);
    }

    private function processQueue(): void
    {
        Queue::assertPushedOn('views', CountView::class, function (CountView $job) {
            $job->handle(new ViewCountManager()); # handle to test updates
            return true;
        });
    }
}
