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
  public static $currentSetKey = 'placecage';

  /** @var \Illuminate\Filesystem\Filesystem */
  public $fileSystem;

  /** @var \Illuminate\Log\LogManager */
  public $logger;

  /** @var array */
  protected static $types = [
    'default',
    'crazy',
    'grayscale',
    'gifs',
  ];

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
    return static::$currentSetKey ?? static::$defaultSetKey;
  }

  /**
   * @return FillSet
   */
  public function currentSet(): FillSet
  {
    return static::$fillSets->getByKey($this->currentSetKey());
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
    $extGlob = ('gifs' === $type) ? "/{$type}/*.gif" : '/*.*';
    return $this->getSourceBase() . $extGlob;
  }

  public function getGeneratedPath(int $width, int $height, ?string $type = null, ?string $setName = null)
  {
    $typeBase = "/{$type}/";
    $dimensions = $width . 'x' . $height;
    $ext = ('gifs' === $type) ? '.gif' : '.jpeg';
    return $this->getGeneratedBase() . $typeBase . $dimensions . $ext;
  }

  public function getGif(int $desiredWidth, int $desiredHeight)
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
    $this->logger->info($hash . " Getting info for " . $fileName);
    $sizeInfo = shell_exec("gifsicle --sinfo {$fileName} | grep 'logical screen'");
    $this->logger->info($hash . " Size Info: " . $sizeInfo);

    if ( 0 === preg_match('/\s(\d+)x(\d+)/', $sizeInfo, $matches)) {
      throw new \Exception($hash . ' Cannot find match.');
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

    $convertResults = shell_exec("gifsicle {$fileName} --resize " . intval($newWidth) . "x" . intval($newHeight) . " | gifsicle --crop " . intval($cropX) . "," . intval($cropY) . "+{$dimensions} --output {$sizedFilepath}");

    return $sizedFilepath;
  }

}