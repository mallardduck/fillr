<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version(); /// TODO: Add a real homepage.
});

$router->group(['middleware' => 'size'], function () use ($router) {
  $routeSize = "/{width:\-?[0-9]{1,4}}/{height:\-?[0-9]{1,4}}";
  $router->get($routeSize, 'ShowImage@show');

  $router->get('/c' . $routeSize, function (int $width, int $height) use ($router) {
      $test = new \App\Services\FillService\FillSet('fillmurray', 'Fill Murray');
      dd( $test->getName() );
      return "Crazy Show Image";
  });

  $router->get('/g' . $routeSize, function (int $width, int $height) use ($router) {
      return "Gray Show Image";
  });

  $router->get('/gif' . $routeSize, 'ShowImage@showGif');
});
