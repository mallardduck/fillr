<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FillService;
use Illuminate\Contracts\View\Factory as View;

class ShowIndex extends Controller
{

    /**
     * @var FillService
     */
    protected $fillr;

    /**
     * Create a new controller instance.
     *
     * @param FillService $fillr
     * @return void
     */
    public function __construct(FillService $fillr)
    {
        $this->fillr = $fillr;
    }

    /**
     * @param  Request $request
     * @param  View    $view
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, View $view)
    {
        $filSet = $this->fillr->getFillSet($request->subdomain);

        return $view->make('indexes.' . $request->subdomain, [
        'fillSet' => $filSet,
        ]);
    }
}
