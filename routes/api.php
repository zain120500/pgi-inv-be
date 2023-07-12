<?php

use App\Http\Controllers\Admin\Barang\BarangStokController;
use App\Http\Controllers\Admin\Barang\BarangTipeController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\Laporan\LaporanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InternalMemo\InternalMemoController;
use App\Http\Controllers\InternalMemo\MaintenanceController;
use App\Http\Controllers\InternalMemo\UserMaintenanceController;
use App\Http\Controllers\InternalMemo\UserMaintenanceVendorController;
use App\Http\Controllers\Transaksi\PemakaianController;
use App\Http\Controllers\Transaksi\StokInventarisController;
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

Route::get('/godmod/{id}', 'GodmodController@godmod')->name('godmod');


Route::post('/attendanceMaintenance/{id}', 'InternalMemo\MaintenanceController@attendanceMaintenance');
Route::post('/webhookTest', 'InternalMemo\MaintenanceController@webhookTest');
Route::post('/forget-password', [AuthController::class, 'submitForgetPasswordForm']);
Route::post('/reset-password', [AuthController::class, 'submitResetPasswordForm']);

Route::group([
    // 'namespace' => 'Profile',
    'prefix' => 'auth'
    // 'middleware' => 'guest',
], function ($router) {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login')->name('login');
    Route::get('/user', 'AuthController@user');
    Route::post('/logout', 'AuthController@logout');
    Route::get('/refresh-token', 'AuthController@refreshToken');
    Route::post('/change-password', 'AuthController@changePassword');
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
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/delete/{id}', [RoleController::class, 'destroy']);
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
        'prefix' => 'karyawan'
    ], function ($router) {
        Route::get('/', [KaryawanController::class, 'all']);
        Route::get('/all', [KaryawanController::class, 'getAllByDivisiId']);
    });

    Route::group([
        // 'namespace' => 'Profile',
        'prefix' => 'management-users'
        // 'middleware' => 'guest',
    ], function ($router) {
        Route::get('/users-role', [UserController::class, 'usersByRole']);
        Route::get('/', [UserController::class, 'index']);
        Route::get('/all', [UserController::class, 'all']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/create', [UserController::class, 'store']);
        Route::put('/update/{id}', [UserController::class, 'update']);
    });



    Route::group([
        'prefix' => 'cabang'
    ], function ($router) {
        Route::get('/', 'CabangController@index');
        Route::get('/all', 'CabangController@all');
        Route::get('/{id}', 'CabangController@show');
        Route::post('/', 'CabangController@store');
        Route::delete('/delete/{id}', 'CabangController@destroy')->name('cabang.delete');
        Route::post('/{id}', 'CabangController@update');
    });

    Route::group([
        'prefix' => 'cabang-user'
    ], function ($router) {
        Route::get('/', 'CabangUserController@index');
        Route::post('/list', 'CabangUserController@getCabangUser');
        Route::post('/{id}', 'CabangUserController@update');
        Route::get('/user-kcs', 'CabangUserController@getUserKCS');
        Route::get('/user-kc', 'CabangUserController@getUserKC');
        Route::get('/user-ku', 'CabangUserController@getUserKU');
        // Route::get('/{id}', 'CabangUserController@show');

        Route::post('/update', 'CabangUserController@userCabangUpdate');


        // Route::post('/', 'CabangUserController@store');
        // Route::delete('/delete/{id}', 'CabangUserController@destroy');
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

            Route::get('/barang-cabang-penerima', 'BarangMasukController@barangByCabangPenerima');
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
            Route::get('/tipe-all', [BarangTipeController::class, 'getBarangTipeAll']);
            Route::get('/all', 'BarangTipeController@all');
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

        Route::group([
            'prefix' => 'barang-stok'
        ], function ($router) {
            Route::get('/asset', [BarangStokController::class, 'assetByIdKategori']);

        });
    });

    Route::group([
        'namespace' => 'Laporan',
        'prefix' => 'laporan'
    ], function ($router) {
        Route::get('/inventaris', [LaporanController::class, 'laporanInvetarisPerorangan']);
        Route::get('/pembelian', [LaporanController::class, 'laporanPembelian']);
        Route::get('/pengiriman', [LaporanController::class, 'laporanPengiriman']);
        Route::get('/pemakaian', [LaporanController::class, 'laporanPemakaian']);
        Route::get('/stokbarang', [LaporanController::class, 'laporanStokBarang']);
        Route::get('/history-barang', [LaporanController::class, 'historyBarang']);
        Route::get('/barang-masuk', [LaporanController::class, 'barangMasuk']);
        Route::get('/barang-keluar', [LaporanController::class, 'barangKeluar']);
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

            Route::group([
                'prefix' => 'cabang'
            ], function ($router) {
                Route::get('/pengirim', 'PengirimanController@cabangPengirim');

            });

            Route::post('/cabang', 'PengirimanController@getCabang');
            Route::post('/cabangPusat', 'PengirimanController@getCabangPusat');
            Route::get('/dropdown-ruangan', 'PengirimanController@dropdownRuangan');

            Route::get('/', 'PengirimanController@index');
            Route::get('/{id}', 'PengirimanController@show');
            Route::post('/', 'PengirimanController@store');

            Route::post('/detail', 'PengirimanController@storeDetail');
            Route::post('/update', 'PengirimanController@updatePengiriman');
            Route::post('/update/detail', 'PengirimanController@updatePengirimanDetail');
            Route::delete('/delete/{id}', 'PengirimanController@destroy');
            Route::delete('/detail/delete/{id}', 'PengirimanController@destroyDetail');

            Route::post('/{id}', 'PengirimanController@update');
            Route::post('/detail/{id}', 'PengirimanController@updateDetail');


        });

        Route::group([
            'prefix' => 'pembelian'
        ], function ($router) {
            Route::get('/create', 'PembelianController@create');
            Route::get('/', 'PembelianController@index');
            Route::get('/detail', 'PembelianController@indexDetail');

            Route::get('/{id}', 'PembelianController@show');
            Route::post('/', 'PembelianController@store');
            Route::post('/detail', 'PembelianController@storeDetail');
            Route::post('/update', 'PembelianController@updatePembelian');
            Route::post('/update/detail', 'PembelianController@updatePembelianDetail');
            Route::delete('/delete/{id}', 'PembelianController@destroy');
            Route::delete('/detail/delete/{id}', 'PembelianController@destroyDetail');

            Route::post('/{id}', 'PembelianController@update');
            Route::post('/detail/{id}', 'PembelianController@updateDetail');

        });

        Route::group([
            'prefix' => 'pemakaian'
        ], function ($router) {
            Route::get('/all', [PemakaianController::class, 'all']);
            Route::get('/paginate', [PemakaianController::class, 'paginate']);
            Route::post('/', [PemakaianController::class, 'store']);
            Route::delete('/{id}', [PemakaianController::class, 'delete']);

        });

        Route::group([
            'prefix' => 'stok-inventaris'
        ], function ($router) {
            Route::get('/all', [StokInventarisController::class, 'all']);
            Route::get('/paginate', [StokInventarisController::class, 'paginate']);
            Route::post('/create', [StokInventarisController::class, 'create']);
            Route::post('/return', [StokInventarisController::class, 'return']);

        });
    });

    Route::group([
        'namespace' => 'InternalMemo',
        'prefix' => 'internal-memo'
    ], function ($router) {

        Route::group([
            'prefix' => 'memo'
        ], function ($router) {

            /**
             * INTERNAL MEMO ACC & DECLINE MEMO ROUTE
             */
            Route::post('/acc/{id}', 'InternalMemoController@accMemo');
            Route::post('/accMemoAll', 'InternalMemoController@accMemoAll');
            Route::post('/ignore/all', 'InternalMemoController@ignoreMemoAll');
            Route::post('/ignore/{id}', 'InternalMemoController@ignoreMemo');
            Route::post('/uploadBuktiPic/{id}', 'InternalMemoController@uploadBuktiPic');
            Route::post('/cancel/{id}', 'InternalMemoController@cancelMemo');

            /**
             * INTERNAL MEMO MAINTENANCE
             */
            Route::get('/getMemoMaintenance/{id}', 'InternalMemoController@getMemoMaintenance');
            Route::get('/cabangByMemoId', 'MaintenanceController@cabangByMemoId');
            Route::get('/barangMerk', 'MaintenanceController@getBarangMerk');
            Route::get('/barangTipe/{id}', 'MaintenanceController@getBarangTipe');
            Route::get('/barangStock', 'MaintenanceController@getBarangStock');
            Route::get('/barangStock', 'MaintenanceController@getBarangStock');
            Route::post('/updateMemoRescheduleV1', 'MaintenanceController@updateMemoRescheduleV1');
            Route::post('/updateMemoRescheduleV2', 'MaintenanceController@updateMemoRescheduleV2');
            Route::post('/updateMemoRescheduleV3', 'MaintenanceController@updateMemoRescheduleV3');
            Route::get('/list-memo-maintenance', 'MaintenanceController@listMemoByMaintenanceLogin');
            Route::post('/konfirmasi-selesai', 'MaintenanceController@konfirmasiSelesai');


            Route::get('/getPusatStock', 'MaintenanceController@getPusatStock');
            Route::post('/updateMemoMaintenance', 'MaintenanceController@updateMemoMaintenance');

            Route::post('/createUserMaitenance', 'MaintenanceController@createUserMaitenance');
            Route::post('/deleteUserMaintenance', 'MaintenanceController@deleteUserMaintenance');
            Route::post('/createBarangMaintenance', 'MaintenanceController@createBarangMaintenance');
            Route::post('/deleteBarangMaintenance', 'MaintenanceController@deleteBarangMaintenance');
            Route::get('/getStockBarangV2', 'MaintenanceController@getStockBarangV2');
            Route::get('/stock-barang-v3', 'MaintenanceController@getStockBarangV3');
            Route::get('/stock-barang-invent', 'MaintenanceController@getStockBarangInvent');
            Route::get('/stock-barang-pemakaian', 'MaintenanceController@getStockBarangPemakaian');
            Route::get('/getListMaintenance', 'MaintenanceController@getListMaintenance');
            Route::post('/surat-tugas-maintenance', [MaintenanceController::class, 'newInternalMaintenance']);
            Route::get('/getDetailBarang', 'MaintenanceController@getDetailBarang');

            /**
             * RATING MEMO
             */
            Route::post('/createInternalRating/{id}', 'InternalMemoController@createInternalRating');
            Route::get('/getRating/{id}', 'InternalMemoController@getRating');

            /**
             * Testing Route
             */
            Route::get('/paginateKuKc', 'InternalMemoController@paginateKuKc');
            Route::get('/menuArchive', 'InternalMemoController@menuArchive');
            Route::get('/menu-archive-pic', [InternalMemoController::class, 'menuArchivePic']);
            Route::post('/createHistoryBarang', 'MaintenanceController@createHistoryBarang');
            Route::post('/whatsuppMessage', 'MaintenanceController@whatsuppMessage');
            Route::get('/im-cabang-id', 'InternalMemoController@imByCabangId');

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
            'prefix' => 'user-maintenance'
        ], function ($router) {
            Route::get('/', [UserMaintenanceController::class, 'all']);
            Route::get('/paginate', [UserMaintenanceController::class, 'paginate']);
            Route::get('/{id}', [UserMaintenanceController::class, 'show']);
            Route::post('/test', [UserMaintenanceController::class, 'store']);
            Route::post('/{id}', [UserMaintenanceController::class, 'update']);
            Route::delete('/delete/{id}', [UserMaintenanceController::class, 'destroy']);
        });

        Route::group([
            'prefix' => 'user-maintenance-vendor'
        ], function ($router) {
            Route::get('/', [UserMaintenanceVendorController::class, 'all']);
            Route::get('/paginate', [UserMaintenanceVendorController::class, 'paginate']);
            Route::get('/{id}', [UserMaintenanceVendorController::class, 'show']);
            Route::post('/', [UserMaintenanceVendorController::class, 'create']);
            Route::post('/{id}', [UserMaintenanceVendorController::class, 'update']);
            Route::delete('/delete/{id}', [UserMaintenanceVendorController::class, 'destroy']);
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

    Route::group([
        'prefix' => 'dashboard'
    ], function ($router) {
        Route::get('/pembelianTrack', 'DashboardController@pembelianTrack');
        Route::get('/stokTersedia', 'DashboardController@stokTersedia');
        Route::get('/stokHabis', 'DashboardController@stokHabis');
    });

});
