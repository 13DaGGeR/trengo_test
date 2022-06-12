<?php

declare(strict_types=1);

namespace App\Models\ArticlesList;

/**
 * todo switch to enum in case of migration to php 8.1+
 */
class SortOrder
{
    public const ORDERS = [
        self::RATING,
        self::VIEWS,
    ];

    public const VIEWS = 'views';
    public const RATING = 'rating';
}
