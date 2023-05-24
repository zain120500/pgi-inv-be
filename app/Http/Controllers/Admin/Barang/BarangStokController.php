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

    public function laporanStokBarang(Request $request)
    {
        $search = $request->search;
        $id_kategori = $request->id_kategori;

        $bStok = StokBarang::whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('kode_barang', 'like', '%' . $search . '%');
            })->whereHas('barangTipe.barangMerk.barangJeniss', function ($q) use ($id_kategori) {
                $q->where('id_kategori', 'like', '%' . $id_kategori . '%');
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

    public function historyBarang(Request $request)
    {
        $nomer_barang = $request->nomer_barang;

        $barangMasuk = DB::table('barang_masuk')
            ->where('barang_masuk.nomer_barang', $nomer_barang)
            ->join('barang_tipe', 'barang_masuk.id_tipe', '=', 'barang_tipe.id')
            ->join('barang_merk', 'barang_tipe.id_merk', '=', 'barang_merk.id')
            ->join('barang_jenis', 'barang_merk.id_jenis', '=', 'barang_jenis.id')
            ->join('cabang', 'barang_masuk.pic', '=', 'cabang.kode')
            ->selectRaw("*, 'Terima' AS keterangan, cabang.name");

        $barangKeluar = DB::table('barang_keluar')
            ->where('barang_keluar.nomer_barang', $nomer_barang)
            ->join('barang_tipe', 'barang_keluar.id_tipe', '=', 'barang_tipe.id')
            ->join('barang_merk', 'barang_tipe.id_merk', '=', 'barang_merk.id')
            ->join('barang_jenis', 'barang_merk.id_jenis', '=', 'barang_jenis.id')
            ->join('cabang', 'barang_keluar.pic', '=', 'cabang.kode')
            ->selectRaw("*, 'Kirim' AS keterangan, cabang.name")
            ->union($barangMasuk)
            ->get();

//        $users = DB::table('users')->selectRaw("*, 'admin' AS type")->get();
//
//        $a = BarangMasuk::where('nomer_barang', '=', $nomer_barang)
//            ->with('barangTipee.barangMerk.barangJenis');
//
//        $b = BarangKeluar::where('nomer_barang', '=', $nomer_barang)->union($a)
//            ->with('barangTipe.barangMerk.barangJenis')->paginate(10);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barangKeluar
        );
    }
}
