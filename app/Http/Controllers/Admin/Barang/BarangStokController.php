<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangJenis;
use App\Model\BarangKeluar;
use App\Model\BarangMasuk;
use App\Model\BarangMerk;
use App\Model\BarangTipe;
use App\Model\StokBarang;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BarangStokController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Aset Tetap Dan Lancar By Id Kategori
     */
    public function assetByIdKategori(Request $request)
    {
        $search = $request->search;
        $id_kategori = $request->id_kategori;

        $bJenis = BarangJenis::where('id_kategori', '=', $id_kategori)->get(); //Fixed Asset
        $bMerek = BarangMerk::whereIn('id_jenis', $bJenis->pluck('id'))->get();
        $bTipe = BarangTipe::whereIn('id_merk', $bMerek->pluck('id'))->get();
        $bStok = StokBarang::whereIn('id_tipe', $bTipe->pluck('id'))
            ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
            $q->where('tipe', 'like', '%' . $search . '%')->orWhere('kode_barang', 'like', '%' . $search . '%');
        })->paginate(15);

        $bStok->map(function ($query) use ($search) {
            $query->cabang;
            $query->barangTipe->barangMerk->barangJeniss->barangKategori;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $bStok
        );
    }
}
