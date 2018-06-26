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
    return view('welcome');
});
Route::any('register','AuthController@register')->name('register');
Route::get('redis','RedisController@test');
Route::get('login','AuthController@index')->name('login');
Route::post('login','AuthController@login')->name('login');
$router->get('logout','AuthController@logout')->name('logout');

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
Route::group(['middleware'=>['auth']],function ($router){
    $router->get('home','HomeController@index')->name('home');
    $router->get('ws/token','WebSocketController@token');
    $router->get('ws/client','WebSocketController@client');
    $router->get('ws/chatroom',function (){
       return view('ws.chatroom');
    });
    $router->get('ws/user/list','WebSocketController@userList');
});
