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

  /**
   * @return FillSet
   */
  public function getFillSet(string $type): FillSet
  {
    return static::$fillSets->getByKey($type);
  }

  /**
   * @return FillSet
   */
  public function setFillSet(string $fillsetName): self
  {
    $this->currentSetKey = $fillsetName;
    return $this;
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

  /**
   * @param  null|string $type
   * @return string
   */
  public function getSourcePath(?string $type = null): string
  {
    $type = $type ?? $this->currentType;
    $extGlob = ('gifs' === $type) ? "/{$type}/*.gif" : '/*.*';
    return $this->getSourceBase() . $extGlob;
  }

  public function getGeneratedPath(int $width, int $height, ?string $type = null, ?string $setName = null): string
  {
    $type = $type ?? $this->currentType;
    $typeBase = $type ? "/{$type}/" : "/";
    $dimensions = $width . 'x' . $height;
    $ext = ('gifs' === $type) ? '.gif' : '.jpeg';
    return $this->getGeneratedBase() . $typeBase . $dimensions . $ext;
  }

  private function getRandomImage(?string $type = null): string
  {
      $searchGlob = $this->getSourcePath($type);
      $fileOptions = $this->fileSystem->glob($searchGlob);
      return array_rand(
                array_flip($fileOptions) // File the keys/values
              ); // Get a random entry from the list
  }
  private function prepateGeometry(int $desiredWidth, int $desiredHeight, array $geo): array
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
        'newWidth'  => $newWidth,
        'newHeight' => $newHeight,
        'cropX'     => $cropX,
        'cropY'     => $cropY,
      ];
  }
  public function getImageFilename(int $desiredWidth, int $desiredHeight, ?string $type = null): string
  {
    $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight, $type);
    if ( $this->fileSystem->exists( $sizedFilepath ) ) {
      return $sizedFilepath;
    } elseif (!extension_loaded('Imagick')) {
        throw new \Exception('Make a new exception later.....');
    }

    $hash = "[" . substr(md5(time()), 9, 7) . "]";
    $dimensions = $desiredWidth . 'x' . $desiredHeight;

    // Get a random image
    $fileName = $this->getRandomImage($type);
    $this->logger->info($hash . " Getting info for " . $fileName);
    $image = new \Imagick($fileName);

    // Get the image size
    $geometry = $image->getImageGeometry();
    $this->logger->info($hash . " Size Info: " . implode('x',$geometry));
    extract($this->prepateGeometry($desiredWidth, $desiredHeight, $geometry));

    // Resize, Crop and Save
    $image->adaptiveResizeImage(intval($newWidth), intval($newHeight));
    $image->cropImage(intval($desiredWidth), intval($desiredHeight), intval($cropX), intval($cropY));
    if ('grayscale' === $this->currentType) {
      $image->setImageColorspace(\Imagick::COLORSPACE_GRAY);
    }
    $image->writeImage($sizedFilepath);

    return $sizedFilepath;
  }

  private function getGifGeometry(string $fileName): array
  {
    $sizeInfo = shell_exec("gifsicle --sinfo {$fileName} | grep 'logical screen'");
    if ( 0 === preg_match('/(\d+)x(\d+)/', $sizeInfo, $matches)) {
      throw new \Exception($hash . ' Cannot find match.');
    }

    return [
      'width' => $matches[1],
      'height' => $matches[2],
    ];
  }
  public function getGifFilename(int $desiredWidth, int $desiredHeight): string
  {
    $sizedFilepath = $this->getGeneratedPath($desiredWidth, $desiredHeight, 'gifs');
    if ( $this->fileSystem->exists( $sizedFilepath ) ) {
      return $sizedFilepath;
    } elseif (is_null(shell_exec('gifsicle -h|head'))) {
        throw new \Exception('Make a new exception later.....');
    }

    $hash = "[" . substr(md5(time()), 9, 7) . "]";
    $dimensions = $desiredWidth . 'x' . $desiredHeight;

    // Get a random image
    $fileName = $this->getRandomImage('gifs');
    $this->logger->info($hash . " Getting info for " . $fileName);

    // Get the image size
    $geometry = $this->getGifGeometry( $fileName );
    $this->logger->info($hash . " Size Info: " . implode('x',$geometry));
    extract($this->prepateGeometry($desiredWidth, $desiredHeight, $geometry));

    // Resize, Crop and Save
    $convertResults = shell_exec("gifsicle {$fileName} --resize " . intval($newWidth) . "x" . intval($newHeight) . " | gifsicle --crop " . intval($cropX) . "," . intval($cropY) . "+{$dimensions} --output {$sizedFilepath}");

    return $sizedFilepath;
  }

}
