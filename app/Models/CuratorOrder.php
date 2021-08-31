<?php

namespace App\Models;

use ArtistRepublik\AROrders\App\Services\CouponCodeService;
use ArtistRepublik\AROrders\Models\CouponCode;
use ArtistRepublik\AROrders\Models\Order;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $client_id
 * @property int $order_id
 * @property int $curator_id
 * @property int $curator_playlist_id
 * @property int $amount
 * @property int $playlist_price
 * @property int $user_track_id
 * @property string $status
 * @property string $status_changed_at
 * @property string $feedback
 * @property int $added_to_playlist
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class CuratorOrder extends Model
{
    use SoftDeletes;

    const RETURN_URL = 'distribution?open=curator';
    const CANCEL_URL = 'distribution?open=curator';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'uuid',
        'api_client_id',
        'external_user_id',
        'user_id',
        'order_id',
        'curator_id',
        'curator_playlist_id',
        'amount',
        'playlist_price',
        'user_track_id',
        'status',
        'status_changed_at',
        'feedback',
        'added_to_playlist',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'status_changed_at' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $order) {
            $order->uuid = Uuid::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeWithPayment($query)
    {
        return $query->join('paymentables', function ($q) {
            $q->on('paymentables.paymentable_id', '=', $this->getTable().'.id')
                    ->where('paymentables.paymentable_type', $this->getMorphClass());
        })
            ->join('payments', 'paymentables.payment_id', '=', 'payments.id');
    }

    public function scopePaid($query)
    {
        return $query->join('orders', 'curator_orders.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['completed', 'partial-refund'])
            ->where('is_refunded', 0);
    }

    public function scopeReviewed($query)
    {
        $query->where('status', '!=', 'pending');
    }

    public function scopeApproved($query)
    {
        $query->whereStatus('approved');
    }

    public function scopeDenied($query)
    {
        $query->whereStatus('denied');
    }

    public function scopeCuratorNotSuspended($query)
    {
        $query->whereHas('curator', function ($query) {
            $query->where('suspended', '=', 0);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function user_track()
    {
        return $this->belongsTo(UserTrack::class);
    }

    public function api_client()
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function curator()
    {
        return $this->belongsTo(Curator::class)->withTrashed();
    }

    public function playlist()
    {
        return $this->belongsTo(CuratorPlaylist::class, 'curator_playlist_id')->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function createOrdersFromPlaylistIds(array $curator_playlist_ids, UserTrack $user_track, User $user, int $coupon_id = null): array
    {
        $curator_orders = [];
        foreach ($curator_playlist_ids as $curator_playlist_id) {
            $curator_playlist = CuratorPlaylist::findOrFail($curator_playlist_id);
            $curator = Curator::where('id', $curator_playlist->curator_id)->first();
            $is_subscribed = $curator->user->subscribed('curator');
            $amount = 0;
            $fee = 0;
            $main_price = $curator_playlist->amount;

            if ($is_subscribed) {
                $fee = round(($main_price) * 0.15, 2, PHP_ROUND_HALF_DOWN);
                $amount = $main_price - $fee;
            } else {
                $amount = $main_price / 2;
                $fee = $main_price / 2;
            }

            if ($coupon_id) {
                $coupon_code = CouponCode::findOrFail($coupon_id);
                $coupon_code_service = new CouponCodeService($coupon_code);

                // Curator always gets full pay cut
                $to_vendor = $amount;
                $fee = $coupon_code_service->discountFee($amount, $fee);
                $main_price = $to_vendor + $fee;
            }

            $curator_order = self::create([
                'api_client_id' => auth()->user()->id,
                'curator_id' => $curator_playlist->curator_id,
                'external_user_id' => $user->external_user_id,
                'user_id' => $user->id,
                'amount' => $amount,
                'playlist_price' => $main_price,
                'curator_playlist_id' => $curator_playlist->id,
                'user_track_id' => $user_track->id,
                'status_changed_at' => now(),
            ]);
            array_push($curator_orders, $curator_order);
        }

        return $curator_orders;
    }

    public static function getCostFromPlaylistIds(array $curator_playlist_ids): float
    {
        $amount = 0;
        foreach ($curator_playlist_ids as $curator_playlist_id) {
            $curator_playlist = CuratorPlaylist::findOrFail($curator_playlist_id);
            $amount += $curator_playlist->amount ? $curator_playlist->amount : convertDollarsToCents($curator_playlist->curator->price);
        }

        return $amount;
    }

    public static function calculateDiscountPrice(CouponCode $coupon_code, $data)
    {
        $playlistIds = explode(',', $data['playlist_ids']);
        $discountPrice = 0;
        foreach ($playlistIds as $playlistId) {
            $curator_playlist = CuratorPlaylist::findOrFail($playlistId);
            $is_subscribed = $curator_playlist->curator->user->subscribed('curator');

            $coupon_code_service = new CouponCodeService($coupon_code);
            $amount = $curator_playlist->amount;
            $fee = $is_subscribed ? round(($amount) * 0.15, 2, PHP_ROUND_HALF_DOWN) : $amount / 2;
            $chargeTotal = $coupon_code_service->discountBuyerCharge((float)($amount - $fee), (float)$fee);
            $discountPrice += round($amount - $chargeTotal, 2);
        }
        return $discountPrice;
    }
}
