<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMasuk;

class BarangMasukController extends Controller
{

    public function index()
    {
        $barang = BarangMasuk::paginate(15);

        $collect = $barang->getCollection()->map(function ($query) {
            return $query->barangTipe;
        });

        return $this->successResponse($barang->setCollection($collect),'Success', 200);
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

    public function barangByCabangKode(Request $request)
    {
        $loginId = auth()->user()->id;
        $cabang = Cabang::where('kepala_cabang_id', $loginId)->orWhere('kepala_cabang_senior_id', $loginId)->orWhere('kepala_unit_id', $loginId)->orWhere('area_manager_id', $loginId)->get()->pluck('kode');

        $bMasuk = BarangMasuk::whereIn('pic', $cabang)->orderBy('id', 'DESC')->paginate(15);

        if($bMasuk){
            return $this->successResponse($bMasuk,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
}
