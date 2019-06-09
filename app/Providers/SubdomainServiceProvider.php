<?php

namespace App\Providers;

use App\Services\SubdomainService;
use Illuminate\Support\ServiceProvider;

class SubdomainServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SubdomainService::class, function ($app) {
            return new SubdomainService();
        });
    }
}
