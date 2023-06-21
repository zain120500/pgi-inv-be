<?php

use App\Http\Controllers\InternalMemo\InternalMemoController;
use App\Http\Controllers\InternalMemo\LaporanInternalMemo;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal Memo Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group([
    'namespace' => 'InternalMemo',
    'prefix' => 'internal-memo'
], function ($router) {

    Route::group([
        'prefix' => 'memo'
    ], function ($router) {

        /**
         * Internal Memo Routes
         */
        Route::get('/dashboard-kc', [InternalMemoController::class, 'dashboardKcImStatus']);
        Route::get('/dashboard-mt', [InternalMemoController::class, 'dashboardMtImStatus']);
        Route::get('/', [InternalMemoController::class, 'index']);
        Route::get('/create', [InternalMemoController::class, 'create']);
        Route::post('/updateFile/{id}', [InternalMemoController::class, 'updateFile']);
        Route::post('/addNewFile/{id}', [InternalMemoController::class, 'addNewFile']);
        Route::get('/{id}', [InternalMemoController::class, 'show']);
        Route::post('/', [InternalMemoController::class, 'store']);
        Route::post('/store-2/test', [InternalMemoController::class, 'store2']);
        Route::post('/{id}', [InternalMemoController::class, 'update']);
        Route::delete('/delete/{id}', [InternalMemoController::class, 'destroy']);

        Route::group([
            'prefix' => 'laporan'
        ], function ($router) {
            Route::get('/perbaikan', [LaporanInternalMemo::class, 'laporanPerbaikan']);
            Route::get('/print-detail-memo/{id}', [LaporanInternalMemo::class, 'printMemoDetail']);
        });
    });
});
