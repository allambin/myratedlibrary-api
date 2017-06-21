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

Route::group(['prefix' => 'v1'], function() {
    Route::post('auth/register', 'Api\Auth\Registercontroller@register')->name('api.v1.auth.register');
    Route::post('auth/login', 'Api\Auth\Logincontroller@login')->name('api.v1.auth.login');
//    ->middleware('auth')
    
    Route::resource('books', 'Api\BookController', [
        'names' => [
            'store' => 'api.v1.books.store',
        ]
    ]);
});