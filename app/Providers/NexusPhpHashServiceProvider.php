<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class NexusPhpHashServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app->make('hash')->extend('nexusphp', function () {
            return new \App\Helpers\NexusPhpHasher();
        });
    }
}
