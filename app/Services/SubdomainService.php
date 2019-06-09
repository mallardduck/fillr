<?php

namespace App\Services;

use App\Models\Subdomain;
use App\Models\FillSettings;
use Illuminate\Support\Collection;

class SubdomainService
{

    /** @var Collection */
    protected static $subdomains;

    /**
     * Construct the Subdomain Service
     */
    public function __construct()
    {
        static::$subdomains = collect([
          new Subdomain(
            'fillmurray',
            [
              'bill',
              'billmurray',
            ],
            new FillSettings('fillmurray', "Fill Murray"),
          ),
          new Subdomain(
            'placecage',
            [
              'nic',
              'niccage',
            ],
            new FillSettings('placecage', "Place Cage", null, [
              'gifs' => true,
              'crazy' => true,
            ]),
          ),
          new Subdomain(
            'stevensegallery',
            [
              'segal',
              'stevensegal',
            ],
            new FillSettings('stevensegallery', "Steven SeGallery"),
          ),
        ]);
    }

    /**
     * @return Collection
     */
    public function getSubdomains(): Collection
    {
      return static::$subdomains;
    }

    /**
     * Returns the subdomain based on the fillset's key.
     *
     * @param  string  $type
     * @return Subdomain
     */
      public function getSubdomainBySetKey(string $type): Subdomain
      {
          return static::$subdomains->getByKey($type);
      }

}
