<?php

namespace App\Models;

use App\Http\Resources\Curator\CuratorPlanResource;
use ArtistRepublik\AROrders\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $name
 * @property int $price
 * @property int $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class CuratorPlan extends Model
{
    public const SUBSCRIBED_PLAYLIST_PRICE = 20.00;

    /**
     * @var array
     */
    protected $fillable = ['name', 'price', 'description', 'created_at', 'updated_at', 'deleted_at'];

    public function resource()
    {
        return new CuratorPlanResource($this);
    }

    public static function handle(User $user, SubscriptionPlan $subscription_plan, $paymentable, string $payment_method)
    {
        if (!$user->subscriptions()->active()->where('name', 'curator')->first()) {
            $subscription = $user->newSubscription('curator', $subscription_plan->stripe_plan)
               ->create($payment_method);
        } else {
            $subscription = $user->subscription('curator')->swap($subscription_plan->stripe_plan);
        }
        if ($user->curator->price < self::SUBSCRIBED_PLAYLIST_PRICE) {
            $user->curator->price = self::SUBSCRIBED_PLAYLIST_PRICE;
        }
        $user->curator->save();
        return $subscription;
    }
}
