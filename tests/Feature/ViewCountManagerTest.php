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

    public function testViewFromNewIpIncreasesCounter(): void
    {
        $article = Article::factory(1)->create()->first();
        $this->assertSame(0, $article->getTotalViews());

        $today = date('Y-m-d');
        $this->assertSame(0, (int)$article->views()->where('date', '>=', $today)->sum('count'));

        $ip = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6';

        $manager = new ViewCountManager();
        $manager->register($article->id, $ip);

        $this->assertSame(1, $article->getTotalViews());
        $this->assertSame(1, (int)$article->views()->where('date', '>=', $today)->sum('count'));
    }

    public function testConsequentViewFromSameIpDoesNotIncreasesCounter(): void
    {
        $article = Article::factory(1)->create()->first();
        $today = date('Y-m-d');
        $ip = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6';

        $manager = new ViewCountManager();
        $manager->register($article->id, $ip);
        $this->assertSame(1, $article->getTotalViews());
        $this->assertSame(1, (int)$article->views()->where('date', '>=', $today)->sum('count'));
        $manager->register($article->id, $ip);
        $this->assertSame(1, $article->getTotalViews());
        $this->assertSame(1, (int)$article->views()->where('date', '>=', $today)->sum('count'));
    }

    public function testConsequentViewFromOtherIpIncreasesCounter(): void
    {
        $article = Article::factory(1)->create()->first();
        $today = date('Y-m-d');
        $ip1 = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6:1';
        $ip2 = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6:2';

        $manager = new ViewCountManager();
        $manager->register($article->id, $ip1);
        $this->assertSame(1, $article->getTotalViews());
        $this->assertSame(1, (int)$article->views()->where('date', '>=', $today)->sum('count'));
        $manager->register($article->id, $ip2);
        $this->assertSame(2, $article->getTotalViews());
        $this->assertSame(2, (int)$article->views()->where('date', '>=', $today)->sum('count'));
    }

    public function testCountWroteToProperDate(): void {
        $this->travel(-1)->day();
        $day1 = now()->toDateString();
        $article = Article::factory(1)->create()->first();
        $ip1 = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6:1';
        $ip2 = 'test:ipv6:test:ipv6:test:ipv6:test:ipv6:2';

        $manager = new ViewCountManager();
        $manager->register($article->id, $ip1);

        $this->travel(1)->day();
        $day2 = now()->toDateString();

        $manager->register($article->id, $ip2);
        $this->assertSame(2, $article->getTotalViews());
        $this->assertSame(1, (int)$article->views()->where('date', '=', $day1)->sum('count'));
        $this->assertSame(1, (int)$article->views()->where('date', '=', $day2)->sum('count'));
    }
}
