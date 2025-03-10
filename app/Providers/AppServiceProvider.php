<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Listen for the Login event
        Event::listen(Login::class, function ($event) {
            $user = $event->user;

            // Set status to online when the user logs in
            $user->update([
                'online' => true,
                'offline' => false,
                'away' => false,
                'do_not_disturb' => false,
            ]);
        });

        // Listen for the Logout event
        Event::listen(Logout::class, function ($event) {
            $user = $event->user;

            // Set status to offline when the user logs out
            $user->update([
                'online' => false,
                'offline' => true,
                'away' => false,
                'do_not_disturb' => false,
            ]);
        });
    }
}
