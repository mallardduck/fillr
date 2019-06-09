<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as View;

class ShowIndex extends Controller
{

    /**
     * @param  Request $request
     * @param  View    $view
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, View $view)
    {
        return $view->make('indexes.' . $request->subdomain->getIndex(), [
          'subdomain' => $request->subdomain,
          'fillSet' => $request->subdomain->getFillSettings(),
        ]);
    }
}
