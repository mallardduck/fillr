<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubdomainSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $parts = explode(".", $request->getHost());
        $subdomain = $parts[0];
        switch ($subdomain) {
          case 'fillmurray':
              $indexFor = 'fillmurray';
              break;
          case 'placecage':
              $indexFor = 'placecage';
              break;
          case 'stevensegallery':
              $indexFor = 'stevensegallery';
              break;
        }
        $request->subdomain = $indexFor;
        app()->subdomain = $indexFor;

        return $next($request);
    }
}
