<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use KnotAShell\Orders\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $product_types = [
            [
                'name' => 'Curator',
                'type' => 'curator'
            ],
            [
                'name' => 'Feature Playlist',
                'type' => 'feature-playlist'
            ],
            [
                'name' => 'Curator Subscription',
                'type' => 'curator-subscription'
            ],
        ];

        if (Schema::hasTable('product_types')) {
            foreach($product_types as $pt) {
                ProductType::firstOrCreate($pt);
            }
        }
    }
}
