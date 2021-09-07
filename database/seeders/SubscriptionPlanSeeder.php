<?php

namespace Database\Seeders;

use App\Models\CuratorPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use KnotAShell\Orders\Models\ProductType;
use KnotAShell\Orders\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $product_type = ProductType::where('type', 'curator-subscription')->first();
        $curator_plan = CuratorPlan::find(1);
        $stripe_plan = App::environment('production') ? 'price_1I7posGVt72o2zxqmq01O5w3' : 'price_1I7poeGVt72o2zxqcbQcSnxq';
        SubscriptionPlan::updateOrCreate(['stripe_plan' => $stripe_plan], [
            'name' => 'Curator Pro',
            'type' => 'curator',
            'product_type_id' => $product_type->id,
            'description' => 'Curator Pro',
            'stripe_plan' => $stripe_plan,
            'planable_id' => $curator_plan->id,
            'planable_type' => $curator_plan->getMorphClass(),
        ]);
    }
}
