<?php

namespace App\Providers;

use App\Events\SpotifyAccountLinked;
use App\Events\UpdateUserSpotify;
use App\Listeners\Spotify\FetchTokenForAccount;
use App\Listeners\Spotify\FinishedSpotifyConnect;
use App\Listeners\Spotify\UpdateAccountInformation;
use App\Listeners\Spotify\UpdatePlaylists;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SpotifyAccountLinked::class => [
            FetchTokenForAccount::class,
        ],
        UpdateUserSpotify::class => [
            UpdateAccountInformation::class,
            UpdatePlaylists::class,
            FinishedSpotifyConnect::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
