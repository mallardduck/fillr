<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CanonicalHeader
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
        $response = $next($request);

        // Build canonical URI
        $canonicalUrl = $request->subdomain->getUriForPath($request->getPathInfo());
        $headerValue = '<' . $canonicalUrl . '>; rel="canonical"';
        // Set header into response - based on response type
        if ($response instanceof Response) {
          $response->header('Link', $headerValue);
        } elseif ($response instanceof BinaryFileResponse) {
          $response->headers->set('Link', $headerValue);
        }

        return $response;
    }
}
