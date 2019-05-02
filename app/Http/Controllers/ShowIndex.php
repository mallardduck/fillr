<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FillService;
use Illuminate\Contracts\View\Factory as View;

class ShowIndex extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FillService $fillr)
    {
        $this->fillr = $fillr;
    }

    public function index(Request $request, View $view)
    {
      $filSet = $this->fillr->getFillSet($request->subdomain);

      return $view->make('indexes.' . $indexFor, [
        'fillSet' => $filSet,
      ]);
    }
}
