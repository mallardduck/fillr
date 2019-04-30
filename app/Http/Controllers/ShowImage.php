<?php

namespace App\Http\Controllers;

use App\Services\FillService;
use Laravel\Lumen\Http\ResponseFactory as Response;

class ShowImage extends Controller
{

    /**
     * @var \App\Services\FillService
     */
    private $fillService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\FillService $fillService
     * @return void
     */
    public function __construct(FillService $fillService)
    {
        $this->fillService = $fillService;
    }

    /**
     * @param  int    $width
     * @param  int    $height
     */
    public function show(int $width, int $height)
    {
      $imagePath = $this->fillService->getGeneratedPath($width, $height);
      dd( $imagePath );
      return response();
      return (new Response)
            ->download(
              $imagePath,
              "$widthx$height",
              [
                'Content-Disposition' => 'inline',
                'Type' => 'image/gif',
              ]
            );
    }

    /**
     * @param  int    $width
     * @param  int    $height
     */
    public function showGif(int $width, int $height)
    {
      $imagePath = $this->fillService->getGif($width, $height);
      return (new Response)
            ->download(
              $imagePath,
              '',
              [
                'Type' => 'image/gif',
              ],
              'inline'
            );
    }
}
