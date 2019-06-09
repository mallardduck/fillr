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

        app()->subdomain = $request->subdomain = $this->indexFromSubdomain($subdomain);

        return $next($request);
    }

    /**
     * @param  string $subdomain
     * @return string
     */
    private function indexFromSubdomain(string $subdomain): string
    {
      switch ($subdomain) {
          case 'billmurray':
          case 'fillmurray':
              return 'fillmurray';
              break;
          case 'niccage':
          case 'placecage':
              return 'placecage';
              break;
          case 'stevensegal':
          case 'stevensegallery':
              return 'stevensegallery';
              break;
      }
    }
}
