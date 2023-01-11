<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangTipe;
use App\Model\BarangMerk;


class BarangTipeController extends Controller
{

    public function index()
    {
        $barang = BarangTipe::paginate(15);
        $collect = $barang->getCollection()->map(function ($query) {
            $query['merk'] = BarangMerk::find($query->id_merk);

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
        $query = BarangTipe::find($id);
        $query['merk'] = BarangMerk::find($query->id_merk);
        $query->barangKeluar;

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
