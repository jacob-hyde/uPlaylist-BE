<?php

namespace Database\Seeders;

use App\Models\CuratorPlan;
use Illuminate\Database\Seeder;

class CuratorPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $data = [
            'name' => 'Pro',
            'price' => 9.99,
            'details' => '<h2 class="font-weight-regular">$9.99/month</h2><ul style="list-style: none; padding-left: 0;"><li>15% fee instead of 50%</li><li>Playlists get a verified blue check mark next to them </li><li>4 days to review instead of 2</li><li>Prices start at $5 instead of $1</li></ul>',
        ];
        CuratorPlan::updateOrCreate(['name' => 'Pro'], $data);
    }
}
