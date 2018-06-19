<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
//    return view('welcome');
});
Route::any('register','AuthController@register');
Route::get('redis','RedisController@test');
Route::get('login','AuthController@index')->name('login');
Route::post('login','AuthController@login');
//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
Route::group(['middleware'=>['auth']],function ($router){
    $router->get('logout','AuthController@logout');
    $router->get('home','HomeController@index');
    $router->get('ws/token','WebSocketController@token');
    $router->get('ws/client','WebSocketController@client');
});
