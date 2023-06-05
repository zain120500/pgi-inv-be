<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangJenis;
use App\Model\BarangMerk;
use App\Model\BarangTipe;
use App\Model\Pemakaian;
use App\Model\Pembelian;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use App\Model\PengirimanKategori;
use App\Model\StokBarang;
use App\Model\StokInventaris;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function laporanInvetarisPerorangan(Request $request)
    {
        $record = StokInventaris::with('barangTipe.barangMerk.barangJeniss', 'karyawan.jabatan')
            ->orderBy('tanggal', 'DESC')
            ->paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function laporanPembelian(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
        $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

        $record = Pembelian::with('detail', 'supplier')
            ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->orderBy('tanggal', 'DESC')
            ->paginate(15);

        if ($request->startDate && $request->endDate) {
            $record = Pembelian::with('detail', 'supplier')
                ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'DESC')
                ->paginate(15);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function laporanPengiriman()
    {
        $record = Pengiriman::with('detail')->orderBy('tanggal', 'DESC')->paginate(15);

        $record->getCollection()->map(function ($q) {
            $details = PengirimanDetail::where('id_pengiriman', $q->id);

            $q['total_unit'] = $details->sum('jumlah');
            $q['total_pembelian'] = $details->sum('total_harga');

            $q['kategori'] = PengirimanKategori::where('id', $q->kategori)->first();
            $q->cabangPengirim;
            $q->cabangPenerima;
            return $q;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function laporanPemakaian(Request $request)
    {
        $search = $request->search;

        $record = Pemakaian::with('barangTipe.barangMerk.barangJeniss')
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('kode_barang', 'like', '%' . $search . '%');
            })
            ->orderBy('tanggal', 'DESC')
            ->paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

        $data = $this->paginate($barangKeluar);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $data
        );
    }

    /**
     * @param $items
     * @param $perPage
     * @param $page
     * @param $options
     * @return LengthAwarePaginator
     */
    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
