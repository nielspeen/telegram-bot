<?php

namespace App\Providers;

use App\Services\Moderator;
use Illuminate\Support\ServiceProvider;

class ModeratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('moderator', function ($app) {
            return new Moderator();
        });
    }

}
