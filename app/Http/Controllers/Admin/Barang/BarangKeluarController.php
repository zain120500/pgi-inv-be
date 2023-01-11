<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangKeluar;
use App\Model\BarangTipe;


class BarangKeluarController extends Controller
{

    public function index()
    {
        $barang = BarangKeluar::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $barang
        ], 200); 
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

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200);  
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
