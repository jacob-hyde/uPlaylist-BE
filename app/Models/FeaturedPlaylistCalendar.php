<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeaturedPlaylistCalendar extends Model
{

    use SoftDeletes;


    protected $table = 'featured_playlist_calendar';

    /**
     * @var array
     */
    protected $fillable = ['curator_playlist_id', 'order_id', 'date', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = [
        'date',
    ];

    public function scopePaid($query)
    {
        return $query->join('orders', 'featured_playlist_calendar.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['completed', 'partial-refund']);
    }

    public function playlist()
    {
        return $this->belongsTo(CuratorPlaylist::class, 'curator_playlist_id');
    }
}
