<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\IpView;
use Database\Factories\IpViewFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViewSeeder extends Seeder
{
    /** @var array<array{ip_address: string, created_at: int, article_id: int}> */
    private array $batch = [];

    private int $batchCn = 0;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedIpView();
        $this->seedArticleView();
    }

    private function seedIpView(): void
    {
        # direct insert used for optimization due to high amount of rows
        $limit = 100000;
        $maxBatchSize = 1000;
        $this->batch = [];
        $this->batchCn = 0;
        $factory = new IpViewFactory();
        do {
            $article = Article::all()->random(1)->first();
            /** @noinspection PhpUnhandledExceptionInspection */
            $number = min(random_int(1000, 10000), $limit);
            for ($i = 0; $i < $number; $i++) {
                $def = $factory->definition();
                $def['article_id'] = $article->id;
                $this->batch[] = $def;
                $this->batchCn++;
                if ($this->batchCn >= $maxBatchSize) {
                    $this->flush();
                }
            }
            $limit -= $number;
        } while ($limit > 0);
        if ($this->batchCn > 0) {
            $this->flush();
        }
    }

    private function flush(): void
    {
        DB::table('ip_views')->insert($this->batch);
        $this->batch = [];
        $this->batchCn = 0;
    }

    private function seedArticleView(): void
    {
        # article_views is basically cache for ip_views, so no generation needed
        DB::statement("
            INSERT INTO article_views
            SELECT * FROM (
                SELECT
                    DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y-%m-%d') AS `date`,
                    article_id,
                    COUNT(*) AS `count`
                FROM ip_views
                GROUP BY `date`, article_id
            ) AS new
            ON DUPLICATE KEY UPDATE article_views.count = new.count;
        ");
    }
}
