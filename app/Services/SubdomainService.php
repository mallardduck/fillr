<?php

namespace App\Services;

use App\Models\Subdomain;
use App\Models\FillSettings;
use Illuminate\Support\Collection;
use App\Exceptions\SubdomainException;

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
            'Fill Murray',
            'fillmurray',
            [
              'bill',
              'billmurray',
            ],
            new FillSettings('fillmurray'),
          ),
          new Subdomain(
            'Place Cage',
            'placecage',
            [
              'nic',
              'niccage',
            ],
            new FillSettings('placecage', null, [
              'gifs' => true,
              'crazy' => true,
            ]),
          ),
          new Subdomain(
            'Steven SeGallery',
            'stevensegallery',
            [
              'segal',
              'stevensegal',
            ],
            new FillSettings('stevensegallery'),
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

      /**
       * A method used to find a subdomain from a given string.
       *
       * @param  string $inputSubdomain
       * @return Subdomain
       */
      public function findSubdomain(string $inputSubdomain): Subdomain
      {
          $subdomain = static::$subdomains->filter(function ($value, $key) use ($inputSubdomain) {
              return $value->subdomainIsMatch($inputSubdomain);
          })->first();
          if (null === $subdomain) {
            throw new SubdomainException("Invalid subdomain used for request.");
          }
          return $subdomain;
      }

      /**
       * A method used to find a sister subdomains from a given string.
       *
       * @param  string $inputSubdomain
       * @return Collection
       */
      public function findSisterSites(string $inputSubdomain): Collection
      {
          return static::$subdomains->filter(function ($value, $key) use ($inputSubdomain) {
              return !$value->subdomainIsMatch($inputSubdomain);
          })->map(function ($value, $key) {
              return [$value->getName(), $value->getCanonicalSchemeAndHost('/')];
          });
      }
}
