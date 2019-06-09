<?php

namespace App\Models;

class Subdomain
{

    /** @var string */
    protected $name;

    /** @var string */
    protected $primarySubdomain;

    /** @var array */
    protected $subdomainAliases;

    /** @var FillSettings */
    protected $fillSettings;

    /**
     * @param string       $name
     * @param string       $subdomain
     * @param array        $aliases
     * @param FillSettings|null $fillSettings
     */
    public function __construct(string $name, string $subdomain = '', array $aliases = [], ?FillSettings $fillSettings = null)
    {
      $this->name = $name;
      $this->primarySubdomain = $subdomain;
      $this->subdomainAliases = $aliases;
      $this->fillSettings    = $fillSettings;
    }

    /**
     * Set the value of Name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the value of Name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of Primary Subdomain
     *
     * @param mixed $primarySubdomain
     *
     * @return self
     */
    public function setPrimarySubdomain($primarySubdomain): self
    {
        $this->primarySubdomain = $primarySubdomain;
        return $this;
    }

    /**
     * Get the value of Primary Subdomain
     *
     * @return mixed
     */
    public function getPrimarySubdomain()
    {
        return $this->primarySubdomain;
    }

    /**
     * Set the value of Subdomain Aliases
     *
     * @param mixed $subdomainAliases
     *
     * @return self
     */
    public function setSubdomainAliases($subdomainAliases): self
    {
        $this->subdomainAliases = $subdomainAliases;
        return $this;
    }

    /**
     * Get the value of Subdomain Aliases
     *
     * @return mixed
     */
    public function getSubdomainAliases()
    {
        return $this->subdomainAliases;
    }

    /**
     * Get the value of Primary Subdomain
     *
     * @return mixed
     */
    public function getAllSubdomains()
    {
        return collect($this->subdomainAliases)->push($this->primarySubdomain);
    }

    /**
     * Set the value of Fill Set
     *
     * @param mixed $fillSettings
     *
     * @return self
     */
    public function setFillSettings($fillSettings): self
    {
        $this->fillSettings = $fillSettings;
        return $this;
    }

    /**
     * Get the value of Fill Set
     *
     * @return mixed
     */
    public function getFillSettings()
    {
        return $this->fillSettings;
    }

    /**
     * Proxies to the fillset's internal method.
     *
     * @return string
     */
    public function getKey(): string
    {
      return $this->fillSettings->getKey();
    }

    /**
     * Provides a check to determine if a string matches this subdomain.
     *
     * @param  string $subdomain An arbitrary subdomain string.
     * @return bool              The bool value indicating if this Subdomain is a fit.
     */
    public function subdomainIsMatch(string $subdomain): bool
    {
        return $this->getAllSubdomains()->search($subdomain, true) !== false;
    }

    /**
     * A proxy method to get the FillSettings Primay Key.
     *
     * @return string The primay app index for this subdomain.
     */
    public function getIndex(): string
    {
        return $this->fillSettings->getKey();
    }

    /**
     * Returns the canonical hostname.
     *
     * @return string
     */
    public function getCanonicalHost(): string
    {
        $baseHttpHost = explode('.', app('request')->getHttpHost());
        $baseHttpHost[0] = $this->getIndex();
        return implode('.', $baseHttpHost);
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getCanonicalSchemeAndHost(): string
    {
        return app('request')->getScheme().'://'.$this->getCanonicalHost();
    }

    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     */
    public function getUriForPath(string $path = '/'): string
    {
        $path = ltrim($path, '/');
        return $this->getCanonicalSchemeAndHost() . '/' . $path;
    }
}
