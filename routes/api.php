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
    Route::get('/all', 'SupplierController@all');
    Route::get('/{id}', 'SupplierController@show');
    Route::post('/', 'SupplierController@store');
    Route::delete('/delete/{id}', 'SupplierController@destroy');
    Route::post('/{id}', 'SupplierController@update');
});

Route::group([
    'prefix' => 'menu'
], function ($router) {
    Route::get('/', 'MenuController@index');
    Route::get('/{id}', 'MenuController@show');
    Route::post('/', 'MenuController@store');
    Route::post('/{id}', 'MenuController@update');
});

Route::group([
    'prefix' => 'topmenu'
], function ($router) {
    Route::get('/', 'TopMenuController@index');
    Route::get('/{id}', 'TopMenuController@show');
    Route::post('/', 'TopMenuController@store');
    Route::post('/{id}', 'TopMenuController@update');
    Route::delete('/delete/{id}', 'TopMenuController@destroy');
});

Route::group([
    'prefix' => 'role-menu'
], function ($router) {        
    Route::get('/all', 'RoleMenuController@all');
    Route::get('/', 'RoleMenuController@index');
    Route::get('/{id}', 'RoleMenuController@show');
    Route::post('/', 'RoleMenuController@store');
    Route::post('/{id}', 'RoleMenuController@update');
    Route::delete('/delete/{id}', 'RoleMenuController@destroy');
});

Route::group([
    'namespace' => 'Admin',
    'prefix' => 'admin',
    'middleware' => 'auth:api'
], function ($router) {

    Route::group([
        'prefix' => 'role'
    ], function ($router) {
        Route::get('/', 'RoleController@index');
        Route::get('/{id}', 'RoleController@show');
        Route::post('/', 'RoleController@store');
        Route::post('/{id}', 'RoleController@update');
        Route::delete('/delete/{id}', 'RoleController@destroy');
    });

    Route::group([
        'prefix' => 'wilayah'
    ], function ($router) {
        Route::get('/kabupaten', 'WilayahController@getKabupatenAll');
        Route::get('/kelurahan', 'WilayahController@getKelurahanAll');
        Route::get('/kecamatan', 'WilayahController@getKecamatanAll');
        Route::get('/provinsi', 'WilayahController@getProvinsiAll');
    });

    Route::group([
        'prefix' => 'devisi'
    ], function ($router) {
        Route::get('/all', 'DevisiController@all');
        Route::get('/', 'DevisiController@index');
        Route::get('/{id}', 'DevisiController@show');
        Route::post('/', 'DevisiController@store');
        Route::post('/{id}', 'DevisiController@update');
        Route::delete('/delete/{id}', 'DevisiController@destroy');
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
        Route::delete('/delete/{id}', 'CabangController@destroy')->name('cabang.delete');
        Route::post('/{id}', 'CabangController@update');

    });


    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'kategori'
        // 'middleware' => 'guest',
    ], function ($router) {

        Route::get('/', 'KategoriController@index');
        Route::get('/{id}', 'KategoriController@show');
        Route::post('/', 'KategoriController@store');
        Route::delete('/delete/{id}', 'KategoriController@destroy')->name('kategory.delete');
        Route::post('/{id}', 'KategoriController@update');
    });

    Route::group([
        'namespace' => 'Barang'
    ], function ($router) {

        Route::group([
            'prefix' => 'barang-jenis'
        ], function ($router) {
            Route::get('/all', 'BarangJenisController@all');

            Route::get('/', 'BarangJenisController@index');
            Route::get('/{id}', 'BarangJenisController@show');
            Route::post('/', 'BarangJenisController@store');
            Route::delete('/delete/{id}', 'BarangJenisController@destroy')->name('barang-jenis.delete');
            Route::post('/{id}', 'BarangJenisController@update');

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
            Route::post('/{id}', 'BarangTipeController@update');

        });

        Route::group([
            'prefix' => 'barang-merk'
        ], function ($router) {
            Route::get('/all', 'BarangMerkController@all');

            Route::get('/', 'BarangMerkController@index');
            Route::get('/{id}', 'BarangMerkController@show');
            Route::post('/', 'BarangMerkController@store');
            Route::delete('/delete/{id}', 'BarangMerkController@destroy')->name('barang-merk.delete');
            Route::post('/{id}', 'BarangMerkController@update');

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

        Route::group([
            'prefix' => 'pengiriman'
        ], function ($router) {
            Route::get('/', 'PengirimanController@index');
            Route::get('/{id}', 'PengirimanController@show');
            Route::post('/', 'PengirimanController@store');
        });

        Route::group([
            'prefix' => 'pembelian'
        ], function ($router) {
            Route::get('/create', 'PembelianController@create');
            Route::get('/', 'PembelianController@index');
            Route::get('/{id}', 'PembelianController@show');
            Route::post('/', 'PembelianController@store');
            Route::post('/detail', 'PembelianController@storeDetail');
            Route::post('/update', 'PembelianController@updatePembelian');
            Route::post('/update/detail', 'PembelianController@updatePembelianDetail');
            Route::delete('/delete/{id}', 'PembelianController@destroy');
            Route::delete('/detail/delete/{id}', 'PembelianController@destroyDetail');
        });
    });

    Route::group([
        'namespace' => 'InternalMemo',
        'prefix' => 'internal-memo'
    ], function ($router) {

        Route::group([
            'prefix' => 'memo'
        ], function ($router) {
            Route::get('/', 'InternalMemoController@index');
            Route::get('/create', 'InternalMemoController@create');

            Route::get('/all', 'InternalMemoController@all');
            Route::get('/{id}', 'InternalMemoController@show');
            Route::post('/', 'InternalMemoController@store');
            Route::post('/{id}', 'InternalMemoController@update');
            Route::delete('/delete/{id}', 'InternalMemoController@destroy');
        });

        Route::group([
            'prefix' => 'kategori-jenis'
        ], function ($router) {
            Route::get('/', 'KategoriJenisController@index');
            Route::get('/all', 'KategoriJenisController@all');
            Route::get('/{id}', 'KategoriJenisController@show');
            Route::post('/', 'KategoriJenisController@store');
            Route::post('/{id}', 'KategoriJenisController@update');
            Route::delete('/delete/{id}', 'KategoriJenisController@destroy');
        });

        Route::group([
            'prefix' => 'kategori'
        ], function ($router) {
            Route::get('/', 'KategoriController@index');
            Route::get('/all', 'KategoriController@all');
            Route::get('/{id}', 'KategoriController@show');
            Route::post('/', 'KategoriController@store');
            Route::post('/{id}', 'KategoriController@update');
            Route::delete('/delete/{id}', 'KategoriController@destroy');
        });

        Route::group([
            'prefix' => 'kategori-sub'
        ], function ($router) {
            Route::get('/', 'KategoriSubController@index');
            Route::get('/all', 'KategoriSubController@all');
            Route::get('/{id}', 'KategoriSubController@show');
            Route::post('/', 'KategoriSubController@store');
            Route::post('/{id}', 'KategoriSubController@update');
            Route::delete('/delete/{id}', 'KategoriSubController@destroy');
        });

        Route::group([
            'prefix' => 'kategori-pic'
        ], function ($router) {
            Route::get('/', 'KategoriPicController@index');
            Route::get('/all', 'KategoriPicController@all');
            Route::get('/{id}', 'KategoriPicController@show');
            Route::post('/', 'KategoriPicController@store');
            Route::post('/{id}', 'KategoriPicController@update');
            Route::delete('/delete/{id}', 'KategoriPicController@destroy');
        });

        Route::group([
            'prefix' => 'devisi-access'
        ], function ($router) {
            Route::get('/', 'DevisiAccessController@index');
            Route::get('/all', 'DevisiAccessController@all');
            Route::get('/{id}', 'DevisiAccessController@show');
            Route::post('/', 'DevisiAccessController@store');
            Route::post('/{id}', 'DevisiAccessController@update');
            Route::delete('/delete/{id}', 'DevisiAccessController@destroy');
        });

        
        
    });

    
});