<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Model\Admin;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use App\Model\PengirimanKategori;
use App\Model\BarangTipe;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::orderBy('tanggal', 'DESC')->paginate(15);
        $collect = $query->getCollection()->map(function ($q) {
            $details = PengirimanDetail::where('id_pengiriman', $q->id);

            $q['total_unit'] = $details->sum('jumlah');
            $q['total_pembelian'] = $details->sum('total_harga');

            $q['kategori'] = PengirimanKategori::where('id', $q->kategori)->first();
            return $q;
        });

        return $this->successResponse($query->setCollection($collect),'Success', 200);

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
        $query = Pengiriman::find($id);

        if(!empty($query->id_user_input)){
            $query['user_input'] = Admin::where('id',$query->id_user_input)->first()->makeHidden(['password']);
        } else {
            $query['user_input'] = Admin::where('username',$query->user_input)->first()->makeHidden(['password']);
        }
        $query['kategori'] = PengirimanKategori::where('id', $query->kategori)->first();

        $pengiriman_detail = PengirimanDetail::where('id_pengiriman', $query->id)->get();
        $collect = $pengiriman_detail->map(function ($q) {
            $q['barang_tipe'] = BarangTipe::where('id', $q->id_tipe)->get();
            return $q;
        });

        $details = PengirimanDetail::where('id_pengiriman', $query->id);
        $query['total_unit'] = $details->sum('jumlah');
        $query['total_pembelian'] = $details->sum('total_harga');
        
        $query['detail'] = $pengiriman_detail;
        
        return $this->successResponse($query,'Success', 200);
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
}
