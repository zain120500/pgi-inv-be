<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangJenis;
use App\Model\BarangMerk;
use App\Model\BarangTipe;
use App\Model\StokBarang;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class BarangStokController extends Controller
{
    public function assetTetap(Request $request)
    {
        $search = $request->search;

        $bJenis = BarangJenis::where('id_kategori', 1)->get(); //Fixed Asset
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

    public function assetLancar(Request $request)
    {
        $search = $request->search;

        $bJenis = BarangJenis::where('id_kategori', 2)->get(); //Current Asset
        $bMerek = BarangMerk::whereIn('id_jenis', $bJenis->pluck('id'))->get();
        $bTipe = BarangTipe::whereIn('id_merk', $bMerek->pluck('id'))->get();
        $bStok = StokBarang::whereIn('id_tipe', $bTipe->pluck('id'))
            ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('kode_barang', 'like', '%' . $search . '%');
            })->paginate(15);

        $bStok->map(function ($query) {
            $query->cabang;
            $query->barangTipe->barangMerk->barangJeniss->barangKategori;

            return $query;
        });

        if ($bStok) {
            return $this->successResponse($bStok, Constants::HTTP_MESSAGE_200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
}
