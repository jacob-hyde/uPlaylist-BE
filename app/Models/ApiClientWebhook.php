<?php

namespace App\Models;

use App\Models\BaseModel;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Model;

class ApiClientWebhook extends Model
{

    protected $table = 'api_client_webhooks';

    protected $fillable = [
        'uuid',
        'url',
        'events',
        'secret',
    ];

    protected $casts = [
        'events' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $api_client_webhook) {
            $api_client_webhook->uuid = Uuid::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function api_client()
    {
        return $this->belongsTo(ApiClient::class);
    }

}
