<?php

declare(strict_types=1);

namespace Articles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function testHappyPath()
    {
        $this->get('/api/articles')
            ->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'page',
                'per_page',
                'total',
            ]);
    }
}
