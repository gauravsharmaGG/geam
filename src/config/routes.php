<?php

Route::group(['prefix' => 'api/v1', 'namespace' => 'App\Http\Controllers'], function () {
    Route::get('test', function () {
    return "YoYo";
	});
});
