<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{

    protected $table = 'help';

    protected $fillable = [
        'question',
        'answer',
        'vendor',
    ];

}
