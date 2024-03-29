<?php

namespace App\Services;

use App\Models\FillSettings;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager as Log;
use App\Services\FillService\ServerException;
use App\Services\FillService\UnsupportedType;

class FillService
{

  /** @var string */
    protected $hash;

  /** @var string */
    protected static $sourceRoot;

  /** @var string */
    protected static $generatedRoot;

  /** @var string */
    public static $defaultSetKey = 'fillmurray';

  /** @var string|void */
    public $currentSetKey = null;

  /** @var \Illuminate\Filesystem\Filesystem */
    public $fileSystem;

  /** @var \Illuminate\Log\LogManager */
    public $logger;

  /** @var array */
    protected static $types = [
    'default' => null,
    'gifs' => 'gifs',
    'crazy' => 'crazy',
    'grayscale' => 'grayscale',
    ];

  /** @var string|void */
    protected $currentType = null;

    public function __construct(Filesystem $fileSystem)
    {
        static::$sourceRoot = storage_path('app/images/source');
        static::$generatedRoot = storage_path('app/images/generated');
        $this->fileSystem = $fileSystem;
        $this->logger = new Log(app());
    }

  /**
   * @return string
   */
    protected function currentSetKey(): string
    {
        return $this->currentSetKey ?? static::$defaultSetKey;
    }

  /**
   * @return FillSettings
   */
    public function currentSet(): FillSettings
    {
        return $this->getFillSettings($this->currentSetKey);
    }

  /**
   * @param  string  $type
   * @return FillSettings
   */
    public function getFillSettings(string $type): FillSettings
    {
        return app(SubdomainService::class)->getSubdomainBySetKey($type)->getFillSettings();
    }

  /**
   * @param  string $fillsetName
   * @return self
   */
    public function setFillSettings(string $fillsetName): self
    {
        $this->currentSetKey = $fillsetName;
        return $this;
    }

  /**
   * @param  string $type
   * @return self
   */
    public function setType(string $type): self
    {
        if (!$this->currentSet()->supports($type)) {
            throw new UnsupportedType('The current fill set does not support the ' . $type . ' type.');
        }
        $this->currentType = static::$types[$type];
        return $this;
    }

  /**
   * @param  string|null $setName
   * @return string
   */
    public function getSourceBase(?string $setName = null): string
    {
        $setFolder = (null === $setName) ?
                    $this->currentSet()->getFolder() :
                    $this->getFillSettings($setName)->getFolder();
        $setFolder = ($this->currentType && 'grayscale' !== $this->currentType) ?
                    $setFolder . '/' . $this->currentType :
                    $setFolder;
        return static::$sourceRoot . '/' . $setFolder;
    }

  /**
   * @param  string|null $setName
   * @return string
   */
    public function getGeneratedBase(?string $setName = null): string
    {
        $setFolder = (null === $setName) ?
                    $this->currentSet()->getFolder() :
                    $this->getFillSettings($setName)->getFolder();
        return static::$generatedRoot . '/' . $setFolder;
    }

  /**
   * @param  null|string $type
   * @return string
   */
    public function getSourcePath(?string $type = null): string
    {
        $type = $type ?? $this->currentType;
        $extGlob = ('gifs' === $type) ? "/*.gif" : '/*.*';
        return $this->getSourceBase() . $extGlob;
    }

  /**
   * Get the image path for a generated image.
   *
   * @param  int          $width
   * @param  int          $height
   * @param  null|string  $type
   * @param  null|string  $setName
   * @return string
   */
    public function getGeneratedPath(int $width, int $height, ?string $type = null, ?string $setName = null): string
    {
        $type = $type ?? $this->currentType;
        $typeBase = $type ? "/{$type}/" : "/";
        $dimensions = $width . 'x' . $height;
        $ext = ('gifs' === $type) ? '.gif' : '.jpeg';
        return $this->getGeneratedBase() . $typeBase . $dimensions . $ext;
    }

  /**
   * Get a random source image.
   *
   * @param  null|string $type
   * @return string
   */
    private function getRandomImage(?string $type = null): string
    {
        $searchGlob = $this->getSourcePath($type);
        $fileOptions = $this->fileSystem->glob($searchGlob);
        return array_rand(
            array_flip($fileOptions) // File the keys/values
        ); // Get a random entry from the list
    }

  /**
   * Prepare the Photo update geometry for the resize.
   *
   * @param  int   $desiredWidth
   * @param  int   $desiredHeight
   * @param  array $geo
   * @return array
   */
    private function prepareGeometry(int $desiredWidth, int $desiredHeight, array $geo): array
    {
        list($width, $height) = array_values($geo);
        $widthRatio = $width / $desiredWidth;
        $heightRatio = $height / $desiredHeight;

        $newWidth = $desiredWidth;
        $newHeight = $desiredHeight;
        if ($heightRatio <= $widthRatio) {
            $newWidth = $width / $heightRatio;
        } else {
            $newHeight = $height / $widthRatio;
        }

        $cropX = ($newWidth - $desiredWidth) / 2;
        $cropY = ($newHeight - $desiredHeight) / 2;
        return [
          $newWidth,
          $newHeight,
          $cropX,
          $cropY,
        ];
    }

  /**
   * Get a sized Image's filename - either by existing or newly generated.
   *
   * @param  int          $desiredWidth
   * @param  int          $desiredHeight
   * @param  null|string  $type
   * @return string
   */
    public function getImageFilename(int $desiredWidth, int $desiredHeight, ?string $type = null): string
    {
        $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight, $type);
        if ($this->fileSystem->exists($sizedFilepath)) {
            return $sizedFilepath;
        } elseif (!extension_loaded('Imagick')) {
            throw new ServerException('The PHP Imagick extension is not installed (or active) on this system.');
        } elseif (count(\Imagick::queryFormats('*')) === 0) {
            throw new ServerException('The PHP Imagick extension cannot access file formats.');
        }

        $this->hash = "[" . substr(md5(time()), 9, 7) . "]";
        $dimensions = $desiredWidth . 'x' . $desiredHeight;

      // Get a random image
        $fileName = $this->getRandomImage($type);
        $this->logger->info($this->hash . " Getting info for " . $fileName);
        $image = new \Imagick($fileName);

      // Get the image size
        $geometry = $image->getImageGeometry();
        $this->logger->info($this->hash . " Size Info: " . implode('x', $geometry));
        list($newWidth, $newHeight, $cropX, $cropY) = $this->prepareGeometry($desiredWidth, $desiredHeight, $geometry);

      // Resize, Crop and Save
        $image->adaptiveResizeImage(intval($newWidth), intval($newHeight));
        $image->cropImage(intval($desiredWidth), intval($desiredHeight), intval($cropX), intval($cropY));
        if ('grayscale' === $this->currentType) {
            $image->setImageColorspace(\Imagick::COLORSPACE_GRAY);
        }
        $image->writeImage($sizedFilepath);

        return $sizedFilepath;
    }

  /**
   * Get the geometry of a Gif - mimicks how Imagick works.
   *
   * @param  string $fileName
   * @return array
   */
    private function getGifGeometry(string $fileName): array
    {
        $sizeInfo = shell_exec("gifsicle --sinfo {$fileName} | grep 'logical screen'");
        if (0 === preg_match('/(\d+)x(\d+)/', $sizeInfo, $matches)) {
            throw new ServerException($this->hash . ' Cannot find match.');
        }

        return [
        'width' => $matches[1],
        'height' => $matches[2],
        ];
    }

  /**
   * Get a sized Gif's filename - either by existing or newly generated.
   *
   * @param  int    $desiredWidth
   * @param  int    $desiredHeight
   * @return string
   */
    public function getGifFilename(int $desiredWidth, int $desiredHeight): string
    {
        $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight);
        if ($this->fileSystem->exists($sizedFilepath)) {
            return $sizedFilepath;
        } elseif (is_null(shell_exec('gifsicle -h|head'))) {
            throw new ServerException('Gifsicle is not installed (or is not accessible) on the system.');
        }

        $this->hash = "[" . substr(md5(time()), 9, 7) . "]";
        $dimensions = $desiredWidth . 'x' . $desiredHeight;

      // Get a random image
        $fileName = $this->getRandomImage('gifs');
        $this->logger->info($this->hash . " Getting info for " . $fileName);

      // Get the image size
        $geometry = $this->getGifGeometry($fileName);
        $this->logger->info($this->hash . " Size Info: " . implode('x', $geometry));
        list($newWidth, $newHeight, $cropX, $cropY) = $this->prepareGeometry($desiredWidth, $desiredHeight, $geometry);

      // Resize, Crop and Save
        $convertResults = shell_exec("gifsicle {$fileName} --resize " . intval($newWidth) . "x" . intval($newHeight) . " | gifsicle --crop " . intval($cropX) . "," . intval($cropY) . "+{$dimensions} --output {$sizedFilepath}");

        return $sizedFilepath;
    }
}
