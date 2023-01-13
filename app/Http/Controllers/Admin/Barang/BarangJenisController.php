<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangJenis;

class BarangJenisController extends Controller
{

    public function index()
    {
        $barang = BarangJenis::paginate(15);

        $collect = $barang->getCollection()->map(function ($query) {
            $query->barangKategori;
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
        $jenis = BarangJenis::create([
            "jenis"=> $request->jenis,
            "id_kategori"=> $request->id_kategori,
            "golongan" => $request->golongan
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $jenis
        ]);
    }

   
    public function show($id)
    {
        $query = BarangJenis::find($id);
        $query->barangKategori;

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
        $query = BarangJenis::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
