<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    protected $fillable = [
        'product_type_id',
        'vendor_id',
        'payment_id',
        'order_id',
        'amount',
        'paid'
    ];
}
