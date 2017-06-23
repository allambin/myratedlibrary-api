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
    
    Route::resource('books', 'Api\BookController', [
        'names' => [
            'store' => 'api.v1.books.store',
            'update' => 'api.v1.books.update',
        ]
    ]);
    
    Route::patch('libraries/{id}', 'Api\LibraryController@patch')->name('api.v1.libraries.patch');
    Route::resource('libraries', 'Api\LibraryController', [
        'names' => [
            'store' => 'api.v1.libraries.store',
            'update' => 'api.v1.libraries.update',
        ]
    ]);
    
    Route::post('libraries/{id}/books', 'Api\LibraryBooksController@addBook')
            ->name('api.v1.libraries.add-book')
            ->where('id', '[0-9]+');
});