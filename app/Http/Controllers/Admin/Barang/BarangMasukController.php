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
        $barang = BarangMasuk::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->paginate(15);

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

    public function barangByCabangPenerima(Request $request)
    {
        $bMasuk = Pengiriman::whereIn('penerima', $this->cabangGlobal()->pluck('kode'))->orderBy('id', 'DESC')->paginate(15);

//        foreach ($bMasuk as $barangMasuk){
//            $q[] = PengirimanDetail::where('id_pengiriman', $barangMasuk->id)->first();
//        }

//        $data = $q;
//        $total = count($q);
//        $perPage = 10; // How many items do you want to display.
//        $currentPage = 1; // The index page.
//        $paginator = new LengthAwarePaginator($data, $total, $perPage, $currentPage);

        if($bMasuk){
            return $this->successResponse($bMasuk,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
}
