<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Views\ViewCountManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewCountManagerTest extends TestCase
{
    use RefreshDatabase;

    public function testViewFromNewIpUpdatesCounter()
    {
        $article = Article::factory(1)->create()->first();
        $this->assertSame(0, $article->getTotalViews());

        $today = date('Y-m-d');
        $this->assertSame(0, $article->views->where('date', '>=', $today)->sum('count'));

        $ip = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6';

        $manager = new ViewCountManager();
        $manager->register($article->id, $ip);

        $this->assertSame(1, $article->getTotalViews());
        $this->assertSame(1, $article->views->where('date', '>=', $today)->sum('count'));
    }
}
