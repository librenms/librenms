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

    // load legacy session, but don't allow it to be updated in laravel code
    session_start();
    session_write_close();

    if (!empty($_SESSION['user_id'])) {
        Auth::onceUsingId($_SESSION['user_id']);
    } else {
        return redirect()->to('/');
    }

    $user = Auth::getUser();
    \App\Checks::postAuth();
    Toastr::info('Welcome ' . ($user->realname ?: $user->username));

    return view('laravel');
});

Route::group(['prefix' => 'ajax'], function () {
    Route::post('set_resolution', 'AjaxController@setResolution');
    Route::post('dash', 'AjaxController@dash');
    Route::post('table', 'AjaxController@table');
    Route::post('form', 'AjaxController@form');
    Route::get('select', 'AjaxController@select');
    Route::post('listports', 'AjaxController@listPorts');
    Route::get('ossuggest', 'AjaxController@osSuggest');
    Route::get('rulesuggest', 'AjaxController@ruleSuggest');
    Route::get('search', 'AjaxController@search');
    Route::get('stream', 'AjaxController@stream');
});

// Debugbar routes need to be here because of catch-all
if (config('app.env') !== 'production' && config('app.debug')) {
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

// Legacy routes
Route::any('/api/v0/{path?}', 'LegacyController@api')->where('path', '.*');
Route::any('/{path?}', 'LegacyController@index')->where('path', '.*');

// should never reach this
Route::any('/{any?}', function ($any = null) {
    echo "Failed to find path\n";
    echo $any . PHP_EOL;
    dd($any);
})->where('any', '.*');
