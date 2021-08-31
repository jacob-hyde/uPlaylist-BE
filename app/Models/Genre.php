<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Genre extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['code', 'name', 'created_at', 'updated_at', 'deleted_at'];

}
