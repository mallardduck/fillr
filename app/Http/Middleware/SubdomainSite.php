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
            case 'billmurray':
            case 'fillmurray':
                $indexFor = 'fillmurray';
                break;
            case 'niccage':
            case 'placecage':
                $indexFor = 'placecage';
                break;
            case 'stevensegal':
            case 'stevensegallery':
                $indexFor = 'stevensegallery';
                break;
        }
        $request->subdomain = $indexFor;
        app()->subdomain = $indexFor;

        return $next($request);
    }
}
