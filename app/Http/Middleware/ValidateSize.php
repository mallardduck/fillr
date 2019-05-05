<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\SizeException;
use Illuminate\Http\Request;

class ValidateSize
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
        $routeWidth = $request->route('width');
        $routeHeight = $request->route('height');
        if ($routeWidth < 1 || $routeHeight < 1) {
          throw new SizeException("Size inputs must be greater than one.");
        }
        if ($routeWidth > 3500 || $routeHeight > 3500) {
          throw new SizeException("Size inputs cannot be larger than 3500.");
        }

        return $next($request);
    }
}
