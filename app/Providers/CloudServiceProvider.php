<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Cloud\OwenCloud;


class CloudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        \App::singleton('cloud', OwenCloud::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
