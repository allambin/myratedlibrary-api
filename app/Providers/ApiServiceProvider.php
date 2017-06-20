<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('App\Libraries\Api\MessageFormatter', function(){
            return new App\Libraries\Api\MessageFormatter();
        });
    }
}
