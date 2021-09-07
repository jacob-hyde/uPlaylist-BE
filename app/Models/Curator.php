<?php

namespace App\Models;

use ArtistRepublik\AROrders\Models\Payment;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $api_client_id
 * @property int $user_id
 * @property int $price
 * @property int $paid_out_amount
 * @property boolean $suspended
 * @property string $suspended_at
 * @property int $no_feedback_count
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Client $client
 */
class Curator extends Model
{
    use SoftDeletes;
    use CascadeSoftDeletes;
    use Filterable;

    public const SUBSCRIBED_PLAYLIST_PRICE = 20.00;

    protected $cascadeDeletes = ['playlists'];

    private static $whiteListFilter =[
        'id',
        'suspended',
        'user_first_name_like',
        'user_last_name_like',
        'user_email_like',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $fillable = ['id', 'api_client_id', 'user_id', 'price', 'paid_out_amount', 'suspended', 'suspended_at', 'no_feedback_count', 'verified', 'verification', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = [
        'last_payout',
    ];

    protected $casts = [
        'verification' => 'json',
    ];

    public function scopeWithPayoutAmount($query)
    {
        return $query->selectRaw('*, ((SELECT
                SUM(co.amount) FROM curator_orders as co
                LEFT JOIN orders as o ON co.order_id = o.id
                WHERE
                (o.status = "completed" OR o.status = "partial-refund")
                AND co.curator_id = curators.id
                AND co.is_refunded = 0
            ) - curators.paid_out_amount) as payout_amount
        ');
    }

    public function getPayoutAmountAttribute()
    {
        $total_payout_possible = CuratorOrder::withPayment()
            ->where('curator_id', $this->id)
            ->where('is_refunded', 0)
            ->whereIn('payments.status', ['paid', 'partial-refunded'])
            ->sum('curator_orders.amount');

        return $total_payout_possible - $this->paid_out_amount;
    }

    public function user_first_name_like($builder, $value)
    {
        return $builder->with(['user'])->whereHas('user', function ($q) use ($value) {
            return $q->where('first_name', 'like', '%'.$value.'%');
        });
    }

    public function user_last_name_like($builder, $value)
    {
        return $builder->with(['user'])->whereHas('user', function ($q) use ($value) {
            return $q->where('last_name', 'like', '%'.$value.'%');
        });
    }

    public function user_email_like($builder, $value)
    {
        return $builder->with(['user'])->whereHas('user', function ($q) use ($value) {
            return $q->where('email', 'like', '%'.$value.'%');
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function api_client()
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function orders()
    {
        return $this->hasMany(CuratorOrder::class);
    }

    public function playlists()
    {
        return $this->hasMany(CuratorPlaylist::class);
    }

    /**
     * Check to see if the curator has been curating for 2 weeks.
     *
     * @param Curator $curator
     * @return bool
     */
    public static function curatorOverTwoWeeks(self $curator): bool
    {
        $first_order = CuratorOrder::where('curator_id', $curator->id)
            ->withPayment()
            ->where('payments.status', Payment::STATUS_PAID)
            ->orderBy('curator_orders.created_at', 'ASC')
            ->limit(1)
            ->select('curator_orders.*')
            ->first();
        if (! $first_order) {
            return false;
        }
        if (now()->diffInDays($first_order->created_at) > 14) {
            return true;
        }

        return false;
    }

    public static function checkPayoutRules(User $user, int $amount)
    {
        if (!$user->curator) {
            return ['CURATOR_ERR_NOT_CURATOR', 'No curator found for this user!'];
        } elseif (!$user->paypal_email) {
            ['ERR_PAYPAL_NOT_SETUP', 'Please add your Paypal Email in settings'];
        } elseif (! Curator::curatorOverTwoWeeks($user->curator)) {
            return ['CURATOR_ERR_NOT_TWO_WEEKS', 'You must be a curator for 2 weeks'];
        } elseif ($user->curator->payout_amount < 2000) {
            return ['CURATOR_ERR_MIN_PAYOUT', 'You must at least have $20 in payouts'];
        } elseif ($user->curator->orders()->withPayment()->where('payments.status', 'paid')->where('curator_orders.status', 'pending')->count() > 0) {
            return ['CURATOR_PENDING_ORDERS', 'You cannot have pending curator orders'];
        }
    }

    public static function getPricing(): array
    {
        $data = [];
        $price = 0;
        for ($i = 0; $i <= 190; $i += 10) {
            $price += 1;
            $data[$i] = $price;
        }

        return $data;
    }

    public static function setCuratorPrice(self $curator): void
    {
        $order_count = $curator->orders->count();
        $prices = self::getPricing();
        if (!empty($curator->user)) {
            if ($order_count > 190) {
                $curator->price = 20;
            } else {
                 if ($curator->user->subscribed('curator')) {
                    $price = self::SUBSCRIBED_PLAYLIST_PRICE;
                    $curator->price = $price;
                } else {
                    $price = $prices[roundDownToNearestTenth($order_count)];
                    $curator->price = $price;
                }
            }
            $curator->save();
        }
    }

}
