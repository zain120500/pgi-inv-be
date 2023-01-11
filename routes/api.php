<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group([
    // 'namespace' => 'Profile',
    'prefix' => 'auth'
    // 'middleware' => 'guest',
], function ($router) {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::get('/user', 'AuthController@user');
});


Route::group([
    'namespace' => 'Admin',
    'prefix' => 'admin',
    'middleware' => 'auth:api',
], function ($router) {

    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'management-users'
        // 'middleware' => 'guest',
    ], function ($router) {

        Route::get('/', 'UserController@index');
        Route::get('/{id}', 'UserController@show');
    });


    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'kategori'
        // 'middleware' => 'guest',
    ], function ($router) {

        Route::get('/', 'KategoriController@index');
        Route::get('/{id}', 'KategoriController@show');
    });


    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'barang-jenis'
    ], function ($router) {

        Route::get('/', 'Barang\BarangJenisController@index');
        Route::get('/{id}', 'Barang\BarangJenisController@show');
    });

    Route::group([
        'prefix' => 'barang-keluar'
    ], function ($router) {

        Route::get('/', 'Barang\BarangKeluarController@index');
        Route::get('/{id}', 'Barang\BarangKeluarController@show');
    });

    Route::group([
        'prefix' => 'barang-tipe'
    ], function ($router) {

        Route::get('/', 'Barang\BarangTipeController@index');
        Route::get('/{id}', 'Barang\BarangTipeController@show');
    });

    

});