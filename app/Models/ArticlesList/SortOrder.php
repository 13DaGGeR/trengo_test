<?php

declare(strict_types=1);

namespace App\Models\ArticlesList;

enum SortOrder: string
{
    case VIEWS = 'views';
    case RATING = 'rating';
}
