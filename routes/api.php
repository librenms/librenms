<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::resource('devices', 'Api\\Device\\DeviceController', [ 'except' => [
    'create',
    'edit'
]]);

Route::get('devices/{id}/health', 'Api\\Device\\DeviceHealthController@show')->name('device.health.show');

Route::resource('ports', 'Api\\PortController', [ 'only' => [
    'index',
    'show'
]]);