<?php

namespace App\Services;

use App\Services\FillService\FillSet;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager as Log;

class FillService {

  /** @var string */
  protected static $sourceRoot;

  /** @var string */
  protected static $generatedRoot;

  /** @var array */
  protected static $fillSets;

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
    'crazy' => 'crazy',
    'grayscale' => 'grayscale',
  ];

  /** @var string|void */
  protected $currentType = null;

  public function __construct(Filesystem $fileSystem)
  {
    static::$sourceRoot = storage_path('app/images/source');
    static::$generatedRoot = storage_path('app/images/generated');
    static::$fillSets = app('fileSets');
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
   * @return FillSet
   */
  public function currentSet(): FillSet
  {
    return static::$fillSets->getByKey($this->currentSetKey());
  }

  public function setType(string $type): self
  {
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
                    $this->currentSet()->getFolder():
                    static::$fillSets->getByKey($setName)->getFolder();
    return static::$sourceRoot . '/' . $setFolder;
  }

  /**
   * @param  string|null $setName
   * @return string
   */
  public function getGeneratedBase(?string $setName = null): string
  {
    $setFolder = (null === $setName) ?
                    $this->currentSet()->getFolder():
                    static::$fillSets->getByKey($setName)->getFolder();
    return static::$generatedRoot . '/' . $setFolder;
  }

  public function getSourcePath(int $width, int $height, ?string $type = null, ?string $setName = null)
  {
    $dimensions = $width . 'x' . $height;
    $type = $type ?? $this->currentType;
    $extGlob = ('gifs' === $type) ? "/{$type}/*.gif" : '/*.*';
    return $this->getSourceBase() . $extGlob;
  }

  public function getGeneratedPath(int $width, int $height, ?string $type = null, ?string $setName = null)
  {
    $type = $type ?? $this->currentType;
    $typeBase = $type ? "/{$type}/" : "/";
    $dimensions = $width . 'x' . $height;
    $ext = ('gifs' === $type) ? '.gif' : '.jpeg';
    return $this->getGeneratedBase() . $typeBase . $dimensions . $ext;
  }

  public function getImageFilename(int $desiredWidth, int $desiredHeight, ?string $type = null)
  {
    $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight, $type);
    if ( $this->fileSystem->exists( $sizedFilepath ) ) {
      return $sizedFilepath;
    }

    $hash = "[" . substr(md5(time()), 9, 7) . "]";
    $dimensions = $desiredWidth . 'x' . $desiredHeight;

    // Get a random image
    $searchGlob = $this->getSourcePath($desiredWidth, $desiredHeight, $type);
    $fileName = array_rand(array_flip($this->fileSystem->glob($searchGlob)));
    $image = new \Imagick($fileName);

    // Get the image size
    $this->logger->info($hash . " Getting info for " . $fileName);
    $geometry = $image->getImageGeometry();
    $this->logger->info($hash . " Size Info: " . implode('x',$geometry));

    list($width, $height) = array_values($geometry);
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

    // Resize, Crop and Save
    $image->adaptiveResizeImage(intval($newWidth), intval($newHeight));
    $image->cropImage(intval($desiredWidth), intval($desiredHeight), intval($cropX), intval($cropY));
    if ('grayscale' === $this->currentType) {
      $image->setImageColorspace(\Imagick::COLORSPACE_GRAY);
    }
    $image->writeImage($sizedFilepath);

    return $sizedFilepath;
  }

  public function getGifFilename(int $desiredWidth, int $desiredHeight)
  {
    $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight, 'gifs');
    if ( $this->fileSystem->exists( $sizedFilepath ) ) {
      return $sizedFilepath;
    }

    $hash = "[" . substr(md5(time()), 9, 7) . "]";
    $dimensions = $desiredWidth . 'x' . $desiredHeight;

    // Get a random image
    $searchGlob = $this->getSourcePath($desiredWidth, $desiredHeight, 'gifs');
    $fileName = array_rand(array_flip($this->fileSystem->glob($searchGlob)));

    // Get the image size
    $this->logger->info($hash . " Getting info for " . $fileName);
    $sizeInfo = shell_exec("gifsicle --sinfo {$fileName} | grep 'logical screen'");
    $this->logger->info($hash . " Size Info: " . $sizeInfo);

    if ( 0 === preg_match('/\s(\d+)x(\d+)/', $sizeInfo, $matches)) {
      if (null === $sizeInfo) {
        throw new \Exception($hash . ' Cannot find gifsicle.');

      } else {
        throw new \Exception($hash . ' Cannot find match.');
      }
    }
    $width = $matches[1];
    $height = $matches[2];
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

    // Resize, Crop and Save
    $convertResults = shell_exec("gifsicle {$fileName} --resize " . intval($newWidth) . "x" . intval($newHeight) . " | gifsicle --crop " . intval($cropX) . "," . intval($cropY) . "+{$dimensions} --output {$sizedFilepath}");

    return $sizedFilepath;
  }

}
