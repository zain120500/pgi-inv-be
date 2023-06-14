<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use App\Model\PengirimanKategori;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMasuk;
use Illuminate\Pagination\LengthAwarePaginator;


class BarangMasukController extends Controller
{

    public function index()
    {
        $query = BarangMasuk::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->paginate(15);

        $collect = $query->getCollection()->map(function ($query) {
            return $query->barangTipe;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $collect
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $query = BarangMasuk::find($id);
        $query->barangTipe;

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function barangByCabangPenerima(Request $request)
    {
        $query = Pengiriman::whereIn('penerima', $this->cabangGlobal()->pluck('kode'))->where('status', 2)->orderBy('id', 'DESC')->paginate(15);

        $query->getCollection()->map(function ($q) {
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
            $query
        );
    }
}
