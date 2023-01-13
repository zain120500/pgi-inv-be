<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMasuk;

class BarangMasukController extends Controller
{
    
    public function index()
    {
        $barang = BarangMasuk::paginate(15);

        $collect = $barang->getCollection()->map(function ($query) {
            $query['tipe'] = BarangTipe::find($query->id_tipe);
            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $barang->setCollection($collect)
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
        $query = BarangMasuk::find($id);
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
