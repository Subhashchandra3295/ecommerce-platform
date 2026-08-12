<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_categories(): void
    {
        Category::factory()->create(['name' => 'Stationery', 'slug' => 'stationery']);

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertJsonFragment(['slug' => 'stationery']);
    }

    public function test_it_lists_products_and_filters_by_category(): void
    {
        $stationery = Category::factory()->create(['slug' => 'stationery']);
        $tech = Category::factory()->create(['slug' => 'tech-gadgets']);

        Product::factory()->create(['category_id' => $stationery->id, 'name' => 'Notebook']);
        Product::factory()->create(['category_id' => $tech->id, 'name' => 'Earbuds']);

        $response = $this->getJson('/api/products?category=stationery');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Notebook'));
        $this->assertFalse($names->contains('Earbuds'));
    }

    public function test_it_shows_a_single_product_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'ceramic-mug']);

        $this->getJson('/api/products/ceramic-mug')->assertOk()->assertJsonFragment(['id' => $product->id]);
    }

    public function test_it_returns_404_for_a_missing_product(): void
    {
        $this->getJson('/api/products/does-not-exist')->assertStatus(404);
    }
}
