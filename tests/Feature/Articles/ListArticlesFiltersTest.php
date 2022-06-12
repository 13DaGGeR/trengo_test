<?php

declare(strict_types=1);

namespace Articles;

use App\Models\Article;
use App\Models\Category;
use App\Models\Search\ArticleIndex\Indexer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListArticlesFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function afterApplicationCreated(callable $callback)
    {
        parent::afterApplicationCreated($callback);
        (new Indexer())->clearIndex();
    }

    protected function callBeforeApplicationDestroyedCallbacks()
    {
        parent::callBeforeApplicationDestroyedCallbacks();
        (new Indexer())->reindex(); # restore search index for non-test rows
    }

    public function testDateFromFilter(): void
    {
        $this->travel(-3)->days();
        $id1 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id2 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id3 = Article::factory(1)->create()->first()->id;

        $date = now()->modify('1 day ago')->format('Y-m-d');
        $response = $this->get(
            '/api/articles?'.http_build_query([
                'date_from' => $date,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(2, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($id2, $ids);
        $this->assertContains($id3, $ids);
        $this->assertNotContains($id1, $ids);
    }

    public function testDateToFilter(): void
    {
        $this->travel(-3)->days();
        $id1 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id2 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id3 = Article::factory(1)->create()->first()->id;

        $date = now()->modify('1 day ago')->format('Y-m-d');
        $response = $this->get(
            '/api/articles?'.http_build_query([
                'date_to' => $date,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(2, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($id1, $ids);
        $this->assertContains($id2, $ids);
        $this->assertNotContains($id3, $ids);
    }

    public function testDatesFilter(): void
    {
        $this->travel(-3)->days();
        $id1 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id2 = Article::factory(1)->create()->first()->id;
        $this->travel(1)->days();
        $id3 = Article::factory(1)->create()->first()->id;

        $date = now()->modify('1 day ago')->format('Y-m-d');
        $response = $this->get(
            '/api/articles?'.http_build_query([
                'date_to' => $date,
                'date_from' => $date,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($id2, $ids);
        $this->assertNotContains($id1, $ids);
        $this->assertNotContains($id3, $ids);
    }


    public function testCategoriesFilter(): void
    {
        $requestedCategory1 = Category::factory(1)->create()->first();
        $requestedCategory2 = Category::factory(1)->create()->first();
        $notRequestedCategory = Category::factory(1)->create()->first();
        $idNoCategory = Article::factory(1)->create()->first()->id;
        $idOneCategory = Article::factory(1)->hasAttached($requestedCategory1)->create()->first()->id;
        $idTwoCategories = Article::factory(1)->hasAttached([$requestedCategory1, $requestedCategory2])
            ->create()->first()->id;
        $idThreeCategories = Article::factory(1)
            ->hasAttached([$requestedCategory1, $requestedCategory2, $notRequestedCategory])->create()->first()->id;
        $idTwoCategoriesWithOneNotRequested = Article::factory(1)
            ->hasAttached([$requestedCategory2, $notRequestedCategory])->create()->first()->id;
        $idWithNotRequestedCategory = Article::factory(1)
            ->hasAttached($notRequestedCategory)->create()->first()->id;

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'categories' => $requestedCategory1->id.','.$requestedCategory2->id,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(4, $json['items']);
        $ids = array_column($json['items'], 'id');
        $this->assertContains($idOneCategory, $ids);
        $this->assertContains($idTwoCategories, $ids);
        $this->assertContains($idThreeCategories, $ids);
        $this->assertContains($idTwoCategoriesWithOneNotRequested, $ids);
        $this->assertNotContains($idNoCategory, $ids);
        $this->assertNotContains($idWithNotRequestedCategory, $ids);
    }

    /**
     * @slow
     * @return void
     */
    public function testFuzzySearch(): void
    {
        [$first, $second] = Article::factory(10)->create()->all();
        $first->title = 'testtesttest';
        $first->save();
        $second->body = 'shmestshmestshmest';
        $second->save();
        sleep(2); # waiting for elasticsearch processes updates

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'q' => '*',
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(10, $json['items']);

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'q' => 'testtestteTS',
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $this->assertContains($first->id, array_column($json['items'], 'id'));

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'q' => 'shmestshmestshEMst',
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $this->assertContains($second->id, array_column($json['items'], 'id'));
    }

    /**
     * @slow
     * @return void
     */
    public function testAllFilters(): void
    {
        $title = 'a_very_testable_title';
        $requestedCategory = Category::factory(1)->create()->first();
        $notRequestedCategory = Category::factory(1)->create()->first();

        $this->travel(-3)->days();
        # too old
        Article::factory(1)->hasAttached($requestedCategory)->create();

        $this->travel(1)->days();

        $ok = Article::factory(1)->hasAttached($requestedCategory)->create()->first();
        $ok->title = $title;
        $ok->save();

        # wrong text
        Article::factory(1)->hasAttached($requestedCategory)->create();

        # wrong category
        $wrongCategory = Article::factory(1)->hasAttached($notRequestedCategory)->create()->first();
        $wrongCategory->title = $title;
        $wrongCategory->save();

        $this->travel(1)->days();
        # too young
        Article::factory(1)->hasAttached($requestedCategory)->create();

        $date = now()->modify('1 day ago')->format('Y-m-d');
        sleep(2); # waiting for elasticsearch processes updates

        $response = $this->get(
            '/api/articles?'.http_build_query([
                'q' => $title,
                'date_to' => $date,
                'date_from' => $date,
                'categories' => $requestedCategory->id,
            ])
        )
            ->assertStatus(200);
        $json = $response->json();
        $this->assertCount(1, $json['items']);
        $this->assertContains($ok->id, array_column($json['items'], 'id'));
    }
}
