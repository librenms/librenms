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

// Auth
Auth::routes();

// WebUI
Route::group(['middleware' => ['auth', '2fa'], 'guard' => 'auth'], function () {
    // Test
    Route::get('/laravel', function () {
        return view('laravel');
    });

    Route::get('locations', 'LocationController@index');

    // old route redirects
    Route::get('poll-log', function () {
        return redirect('pollers/tab=log/');
    });

    // Two Factor Auth
    Route::get('2fa', 'TwoFactorController@showTwoFactorForm')->name('2fa.form');
    Route::post('2fa', 'TwoFactorController@verifyTwoFactor')->name('2fa.verify');
    Route::post('2fa/add', 'TwoFactorController@create');
    Route::post('2fa/cancel', 'TwoFactorController@cancelAdd')->name('2fa.cancel');
    Route::post('2fa/remove', 'TwoFactorController@destroy');

    // Ajax routes
    Route::group(['prefix' => 'ajax'], function () {
        Route::post('set_resolution', 'ResolutionController@set');
        Route::resource('location', 'LocationController', ['only' => ['update', 'destroy']]);

        Route::group(['prefix' => 'select', 'namespace' => 'Select'], function () {
            Route::get('device', 'DeviceController');
            Route::get('eventlog', 'EventlogController');
            Route::get('syslog', 'SyslogController');
        });

        Route::group(['prefix' => 'table', 'namespace' => 'Table'], function () {
            Route::post('eventlog', 'EventlogController');
            Route::post('location', 'LocationController');
            Route::post('syslog', 'SyslogController');
        });
    });

    // Debugbar routes need to be here because of catch-all
    if (config('app.env') !== 'production' && config('app.debug') && config('debugbar.enabled') !== false) {
        Route::get('/_debugbar/assets/stylesheets', [
            'as' => 'debugbar-css',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@css'
        ]);

        Route::get('/_debugbar/assets/javascript', [
            'as' => 'debugbar-js',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@js'
        ]);

        Route::get('/_debugbar/open', [
            'as' => 'debugbar-open',
            'uses' => '\Barryvdh\Debugbar\Controllers\OpenController@handler'
        ]);
    }

    // demo helper
    Route::get('demo', function () {
        return redirect('/');
    });

    // Legacy routes
    Route::any('/{path?}', 'LegacyController@index')->where('path', '.*');
});
