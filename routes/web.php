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

$router->group(['middleware' => 'domain'], function () use ($router) {
  $router->get('/', 'ShowIndex');

  $router->group(['middleware' => 'size'], function () use ($router) {
    $routeSize = "/{width:\-?[0-9]+}/{height:\-?[0-9]+}";
    $router->get($routeSize, 'ShowImage@show');
    $router->get('/g' . $routeSize, 'ShowImage@showGray');
    $router->get('/c' . $routeSize, 'ShowImage@showCrazy');
    $router->get('/gif' . $routeSize, 'ShowImage@showGif');
    $router->get('/gifs' . $routeSize, 'ShowImage@showGif');
  });
});
