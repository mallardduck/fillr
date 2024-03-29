<?php

namespace App\Models;

use \Error;

class FillSettings
{

  /** @var bool */
    protected $supportsGifs = false;
  /** @var bool */
    protected $supportsCrazy = false;
  /** @var bool */
    protected $supportsGrayscale = true;

  /** @var string */
    protected $key;
  /** @var string */
    protected $folder;

  /**
   * @param string      $key      The Image Set's app key.
   * @param string|null $folder   The Image Set's folder, if different than key. Optional.
   * @param array|null  $supports The array of supports options. Optional.
   */
    public function __construct(string $key, ?string $folder = null, ?array $supports = [])
    {
        $this->key = $key;
        $this->folder = $folder ?? $key;
        if (count($supports)) {
            foreach ($supports as $support => $value) {
                if (property_exists(self::class, ($property = 'supports' . ucfirst($support)))) {
                    $this->{$property} = $value;
                }
            }
        }
    }

  /**
   * Add magic method to access protected properties.
   *
   * @param  string $method
   * @param  array $args
   * @return mixed
   */
    public function __call(string $method, array $args)
    {
        $results = preg_match('/^get([A-Z][a-zA-Z]*)/', $method, $methodPart);
        if ((bool) $results && ($prop = \lcfirst($methodPart[1])) && property_exists(self::class, $prop)) {
            return $this->{$prop};
        }
        throw new Error('Call to undefined method ' . static::class . '::' . $method . '()');
    }

  /**
   * @param  string $type
   * @return bool
   */
    public function supports(string $type): bool
    {
        if ('gif' === $type) {
            $type = $type . 's';
        }
        $prop = 'supports' . ucfirst(strtolower($type));
        return $this->{$prop};
    }
}
