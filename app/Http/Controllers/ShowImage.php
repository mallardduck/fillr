<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * @param  Request $request
     * @param  int    $width
     * @param  int    $height
     */
    public function show(Request $request, int $width, int $height)
    {
        $this->fillService->setFillSettings($request->subdomain->getIndex());
        $imagePath = $this->fillService->getImageFilename($width, $height);
        return (new Response())
            ->download(
                $imagePath,
                '',
                [
                'Type' => 'image/jpeg',
                ],
                'inline'
            );
    }

    /**
     * @param  int    $width
     * @param  int    $height
     */
    public function showGray(Request $request, int $width, int $height)
    {
        $this->fillService->setFillSettings($request->subdomain->getIndex());
        $imagePath = $this->fillService->setType('grayscale')->getImageFilename($width, $height);
        return (new Response())
            ->download(
                $imagePath,
                '',
                [
                'Type' => 'image/jpeg',
                ],
                'inline'
            );
    }

    /**
     * @param  int    $width
     * @param  int    $height
     */
    public function showCrazy(Request $request, int $width, int $height)
    {
        $this->fillService->setFillSettings($request->subdomain->getIndex());
        $imagePath = $this->fillService->setType('crazy')->getImageFilename($width, $height);
        return (new Response())
            ->download(
                $imagePath,
                '',
                [
                'Type' => 'image/jpeg',
                ],
                'inline'
            );
    }

    /**
     * @param  int    $width
     * @param  int    $height
     */
    public function showGif(Request $request, int $width, int $height)
    {
        $this->fillService->setFillSettings($request->subdomain->getIndex());
        $imagePath = $this->fillService->setType('gifs')->getGifFilename($width, $height);
        return (new Response())
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
