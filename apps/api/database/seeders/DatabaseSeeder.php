<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@shopcraft.test'],
            ['name' => 'Store Admin', 'password' => bcrypt('password'), 'is_admin' => true],
        );
        $admin->cart()->firstOrCreate();

        $catalog = [
            'Home & Living' => [
                ['Ceramic Coffee Mug', 1400, 'Hand-glazed stoneware mug, holds 12oz.'],
                ['Soy Wax Candle', 1800, 'Small-batch soy candle, cedar & sandalwood.'],
                ['Chunky Knit Throw Blanket', 5900, 'Oversized cotton-blend throw for the couch.'],
            ],
            'Stationery' => [
                ['Leather Journal', 2600, 'Refillable leather-bound notebook, dot grid.'],
                ['Brass Fountain Pen', 3400, 'Fine nib, converts to cartridge or ink.'],
                ['Walnut Desk Organizer', 4200, 'Solid walnut tray for pens, cards, and cables.'],
            ],
            'Accessories' => [
                ['Canvas Tote Bag', 2200, 'Heavyweight canvas, leather handles.'],
                ['Merino Wool Beanie', 1900, 'Soft merino wool, one size fits most.'],
                ['Minimalist Leather Wallet', 3800, 'Full-grain leather, 6-card capacity.'],
            ],
            'Tech Gadgets' => [
                ['Wireless Earbuds', 6900, 'Active noise cancellation, 24hr battery.'],
                ['Portable USB-C Charger', 3200, '10,000mAh, fits in a pocket.'],
                ['Bluetooth Speaker', 4500, 'Water-resistant, 12hr playtime.'],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName],
            );

            foreach ($products as [$name, $priceCents, $description]) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $description,
                        'price_cents' => $priceCents,
                        'stock' => random_int(15, 60),
                    ],
                );
            }
        }
    }
}
