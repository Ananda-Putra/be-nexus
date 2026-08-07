<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronics = Category::where('slug', 'electronics')->first();
        $fashion = Category::where('slug', 'fashion')->first();
        $homeGarden = Category::where('slug', 'home-garden')->first();
        $sportsOutdoors = Category::where('slug', 'sports-outdoors')->first();
        $healthBeauty = Category::where('slug', 'health-beauty')->first();

        $products = [
            [
                'name' => 'Laptop',
                'slug' => 'laptop',
                'category_id' => $electronics->id,
                'description' => 'Laptop untuk kebutuhan kerja dan gaming',
                'price' => 8500000,
                'stock' => 10,
                'image_url' => 'https://i.pcmag.com/imagery/reviews/032Ghc5tCjiCya7cxiW3B5O-11.jpg',
            ],
            [
                'name' => 'Shoes',
                'slug' => 'shoes',
                'category_id' => $fashion->id,
                'description' => 'Sepatu casual sehari-hari',
                'price' => 350000,
                'stock' => 25,
            ],
            [
                'name' => 'Table',
                'slug' => 'table',
                'category_id' => $homeGarden->id,
                'description' => 'Meja kayu minimalis',
                'price' => 750000,
                'stock' => 8,
            ],
            [
                'name' => 'Ball',
                'slug' => 'ball',
                'category_id' => $sportsOutdoors->id,
                'description' => 'Bola sepak ukuran standar',
                'price' => 150000,
                'stock' => 30,
            ],
            [
                'name' => 'Medicine',
                'slug' => 'medicine',
                'category_id' => $healthBeauty->id,
                'description' => 'Obat vitamin harian',
                'price' => 45000,
                'stock' => 100,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
