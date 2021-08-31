<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class VendorPayoutHistory extends Model
{
    protected $table = 'vendor_payout_history';

    protected $fillable = [
        'product_type_id',
        'date_start',
        'date_end',
        'amount'
    ];
}
