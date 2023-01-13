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
    'prefix' => 'supplier'
], function ($router) {
    Route::get('/', 'SupplierController@index');
    Route::get('/{id}', 'SupplierController@show');
    Route::post('/', 'SupplierController@store');
});

Route::group([
    'namespace' => 'Admin',
    'prefix' => 'admin',
    'middleware' => 'auth:api',
], function ($router) {


    Route::group([
        'prefix' => 'role'
    ], function ($router) {
        Route::get('/', 'RoleController@index');
        Route::get('/{id}', 'RoleController@show');
        Route::post('/', 'RoleController@store');
    });

    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'management-users'
        // 'middleware' => 'guest',
    ], function ($router) {

        Route::get('/', 'UserController@index');
        Route::get('/{id}', 'UserController@show');
    });

    
    Route::group([
        'prefix' => 'cabang'
    ], function ($router) {
        Route::get('/', 'CabangController@index');
        Route::get('/{id}', 'CabangController@show');
        Route::post('/', 'CabangController@store');

    });


    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'kategori'
        // 'middleware' => 'guest',
    ], function ($router) {

        Route::get('/', 'KategoriController@index');
        Route::get('/{id}', 'KategoriController@show');
        Route::post('/', 'KategoriController@store');

    });

    Route::group([
        'namespace' => 'Barang'
    ], function ($router) {

        Route::group([
            'prefix' => 'barang-jenis'
        ], function ($router) {

            Route::get('/', 'BarangJenisController@index');
            Route::get('/{id}', 'BarangJenisController@show');
            Route::post('/', 'BarangJenisController@store');
            Route::delete('/delete/{id}', 'BarangJenisController@destroy')->name('barang-jenis.delete');
        });

        Route::group([
            'prefix' => 'barang-masuk'
        ], function ($router) {

            Route::get('/', 'BarangMasukController@index');
            Route::get('/{id}', 'BarangMasukController@show');
        });
        
        Route::group([
            'prefix' => 'barang-keluar'
        ], function ($router) {

            Route::get('/', 'BarangKeluarController@index');
            Route::get('/{id}', 'BarangKeluarController@show');
        });

        Route::group([
            'prefix' => 'barang-tipe'
        ], function ($router) {
            Route::get('/', 'BarangTipeController@index');
            Route::get('/{id}', 'BarangTipeController@show');
            Route::post('/', 'BarangTipeController@store');
            Route::delete('/delete/{id}', 'BarangTipeController@destroy')->name('barang-tipe.delete');

        });

        Route::group([
            'prefix' => 'barang-merk'
        ], function ($router) {
            Route::get('/', 'BarangMerkController@index');
            Route::get('/{id}', 'BarangMerkController@show');
            Route::post('/', 'BarangMerkController@store');
            Route::delete('/delete/{id}', 'BarangMerkController@destroy')->name('barang-merk.delete');

        });
    });
});



Route::group([
    // 'namespace' => 'Admin',
    // 'prefix' => 'admin',
    'middleware' => 'auth:api',
], function ($router) {

    Route::group([
        'namespace' => 'Transaksi',
        'prefix' => 'transaksi'
    ], function ($router) {

        Route::group([
            'prefix' => 'dropshipper'
        ], function ($router) {
            Route::get('/', 'DropshipperController@index');
            Route::get('/{id}', 'DropshipperController@show');
            Route::post('/', 'DropshipperController@store');
        });
    });

    
});