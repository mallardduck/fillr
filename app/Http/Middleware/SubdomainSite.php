<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\SubdomainService;

use Illuminate\Http\Request;

class SubdomainSite
{

    /** @var SubdomainService */
    private $subdomainService;

    /**
     * @param SubdomainService $subdomainService
     */
    public function __construct(SubdomainService $subdomainService)
    {
      $this->subdomainService = $subdomainService;
    }

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

        $request->subdomain   = $this->subdomainService->findSubdomain($subdomain);
        $request->sisterSites = $this->subdomainService->findSisterSites($subdomain);

        return $next($request);
    }
}
