<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangKeluar;
use App\Model\BarangTipe;
use App\Helpers\Constants;


class BarangKeluarController extends Controller
{

    public function index()
    {
        $barang = BarangKeluar::paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barang
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
        $query = BarangKeluar::find($id);
        $query['tipe'] = BarangTipe::find($query->id_tipe);

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
}
