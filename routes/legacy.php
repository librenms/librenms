<?php


Route::group(['middleware' => ['auth'], 'guard' => 'auth'], function () {
    Route::any('legacy_ajax_dash', 'LegacyController@dash');
});
