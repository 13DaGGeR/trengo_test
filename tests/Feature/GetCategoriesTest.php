<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function testHappyPath(): void
    {
        $categories = Category::factory(2)->create()->all();
        $categoriesAsArray = [];
        foreach ($categories as $category) {
            $categoriesAsArray[] = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        }

        $this->get('/api/categories/')
            ->assertStatus(200)
            ->assertJson($categoriesAsArray, true);
    }
}
