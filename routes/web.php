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

Route::get('/laravel', function () {
    return view('welcome');
});

Route::any('/api/v0/{path?}', 'LegacyController@api')->where('path', '.*');
Route::any('/{path?}', 'LegacyController@index')->where('path', '.*');

Route::any('/{any?}', function ($any = null) {
    dd($any);
})->where('any', '.*');
