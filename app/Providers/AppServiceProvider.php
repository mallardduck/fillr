<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
      Collection::macro('getByKey', function ($searchKey) {
          return $this->first(function ($value, $key) use ($searchKey) {
              return $value->getKey() === $searchKey;
          });
      });
    }
}
