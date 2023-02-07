<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangTipe;
use App\Model\BarangMerk;
use App\Helpers\StatusHelper;

class BarangTipeController extends Controller
{

    public function index(Request $request)
    {        
        if(!empty($request->tipe)) {
            $barang = BarangTipe::where('tipe', 'like', '%'.$request->tipe.'%')->paginate(15);
        } else if(!empty($request->kode_barang)){
            $barang = BarangTipe::where('kode_barang', 'like', '%'.$request->kode_barang.'%')->paginate(15);
        } else {
            $barang = BarangTipe::paginate(15);
        }

        $collect = $barang->getCollection()->map(function ($query) {
            $barangMerk = $query->barangMerk;
            if(!empty($barangMerk)){
                $barangMerk->barangJenis;
            }

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $barang->setCollection($collect)
        ], 200); 

    }

    public function all(Request $request)
    {        
        if(!empty($request->tipe)) {
            $barang = BarangTipe::where('tipe', 'like', '%'.$request->tipe.'%')->get();
        } else if(!empty($request->kode_barang)){
            $barang = BarangTipe::where('kode_barang', 'like', '%'.$request->kode_barang.'%')->get();
        } else {
            $barang = BarangTipe::all();
        }

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
        $tipe = BarangTipe::create([
            "tipe"=> $request->tipe,
            "satuan"=> $request->satuan,
            "harga"=> $request->harga,
            "tipe_kode"=> $request->tipe_kode,
            "kode_barang"=> $request->kode_barang,
            "id_merk"=> $request->id_merk
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $tipe
        ]);
    }

    public function show($id)
    {
        $query = BarangTipe::find($id);

        if(!empty($query)){
            $barangMerk = $query->barangMerk;
            if(!empty($barangMerk)){
                $barangMerk->barangJenis;
            }
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }

    }

    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $query = BarangTipe::where('id', $id)
            ->update([
            "tipe"=> $request->tipe,
            "satuan"=> $request->satuan,
            "harga"=> $request->harga,
            "tipe_kode"=> $request->tipe_kode,
            "kode_barang"=> $request->kode_barang,
            "id_merk"=> $request->id_merk
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }


    public function destroy($id)
    {
        $query = BarangTipe::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
