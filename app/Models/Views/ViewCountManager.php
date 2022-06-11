<?php

declare(strict_types=1);

namespace App\Models\Views;

use Illuminate\Support\Facades\DB;

class ViewCountManager
{
    public function register(int $articleId, string $ip): void
    {
        $now = now()->timestamp;
        $isNew = IpView::insertOrIgnore([
            'article_id' => $articleId,
            'ip_address' => $ip,
            'created_at' => $now,
        ]) > 0;
        if ($isNew) {
            # use raw SQL to limit number of queries
            DB::statement('
                INSERT INTO article_views
                SET
                    article_id = :article_id,
                    `date` = :date,
                    `count` = 1
                ON DUPLICATE KEY UPDATE
                    `count` = `count` + 1
            ', [
                'date' => date('Y-m-d', $now),
                'article_id' => $articleId,
            ]);
        }
    }
}
