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
  $routeSize = "/{width:[\-0-9]{1,4}}/{height:[0-9]{1,4}}";
  $router->get($routeSize, function ($width, $height) use ($router) {
      return "Regular Show Image"; /// TODO: Add a real homepage.
  });

  $router->get('/c' . $routeSize, function ($width, $height) use ($router) {
      return "Crazy Show Image"; /// TODO: Add a real homepage.
  });

  $router->get('/g' . $routeSize, function ($width, $height) use ($router) {
      return "Gray Show Image"; /// TODO: Add a real homepage.
  });

  $router->get('/gif' . $routeSize, function ($width, $height) use ($router) {
      return "Gif Show Image"; /// TODO: Add a real homepage.
  });
});
