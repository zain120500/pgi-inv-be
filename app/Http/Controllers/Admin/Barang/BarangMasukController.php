<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMasuk;
use Illuminate\Pagination\LengthAwarePaginator;

class BarangMasukController extends Controller
{

    public function index()
    {
        foreach ($this->cabangGlobal() as $cabang){
            if($cabang->lokasi == 2){
                $query = Pengiriman::orderBy('tanggal', 'DESC')->paginate(15);
            }else{
                $query = BarangMasuk::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->paginate(15);

                $collect = $query->getCollection()->map(function ($query) {
                    return $query->barangTipe;
                });
            }

            if($query){
                return $this->successResponse($query->setCollection($collect),'Success', 200);
            } else {
                return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
            }
        }
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

    public function barangByCabangPenerima(Request $request)
    {
        foreach ($this->cabangGlobal() as $cabang){
            if($cabang->lokasi == 2){
                $query = Pengiriman::whereIn('penerima', $this->cabangGlobal()->pluck('kode_cabang'))->orderBy('tanggal', 'DESC')->paginate(15);
            }else{
                $query = Pengiriman::whereIn('penerima', $this->cabangGlobal()->pluck('kode'))->orderBy('id', 'DESC')->paginate(15);
            }

            if($query){
                return $this->successResponse($query,Constants::HTTP_MESSAGE_200, 200);
            } else {
                return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
            }
        }
    }
}
