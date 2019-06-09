<?php

namespace App\Models;

class Subdomain
{
    /** @var string */
    protected $primarySubdomain;

    /** @var array */
    protected $subdomainAliases;

    /** @var FillSettings */
    protected $fillSettings;

    /**
     * @param string       $subdomain
     * @param array        $aliases
     * @param FillSettings|null $fillSettings
     */
    public function __construct(string $subdomain = '', array $aliases = [], ?FillSettings $fillSettings = null)
    {
      $this->primarySubdomain = $subdomain;
      $this->subdomainAliases = $aliases;
      $this->fillSettings    = $fillSettings;
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
     * Set the value of Primary Subdomain
     *
     * @param mixed $primarySubdomain
     *
     * @return self
     */
    public function setPrimarySubdomain($primarySubdomain)
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
    public function setSubdomainAliases($subdomainAliases)
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
    public function setFillSettings($fillSettings)
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
     * Provides a check to determine if a string matches this subdomain.
     *
     * @param  string $subdomain An arbitrary subdomain string.
     * @return bool              The bool value indicating if this Subdomain is a fit.
     */
    public function subdomainIsMatch(string $subdomain): bool
    {
        return $this->getAllSubdomains()->search($subdomain, true) !== false;
    }
}
