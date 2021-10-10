<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use JacobHyde\Tickets\App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $data = [
            'Placing an order',
            'Existing Order',
            'Vendor Help',
            'Refund Request',
            'Curator Pro Issue',
            'Curator Problem',
            'Business Inquirie',
        ];
        foreach ($data as $value) {
            Category::updateOrCreate(['name' => $value], ['name' => $value]);
        }
    }
}
