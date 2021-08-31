<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductType extends Model
{
    use SoftDeletes;

    protected $table = 'product_types';

    protected $fillable = [
        'name',
        'type',
    ];

}
