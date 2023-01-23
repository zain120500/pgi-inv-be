<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMerk;

class BarangMerkController extends Controller
{
    
    public function index(Request $request)
    {
        if(!empty($request->merk)){
            $barang = BarangMerk::where('merk', 'like', '%'.$request->merk.'%')->paginate(15);
        } else {
            $barang = BarangMerk::paginate(15);
        }

        $collect = $barang->getCollection()->map(function ($query) {
            $query->barangJenis;
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
        $merk = BarangMerk::create([
            "merk"=> $request->merk,
            "id_jenis"=> $request->id_jenis
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $merk
        ]);
    }

    public function show($id)
    {
        $query = BarangMerk::find($id);

        if(!empty($query)){
            $query->barangJenis;

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
        $query = BarangMerk::where('id', $id)
            ->update([
                "merk"=> $request->merk,
                "id_jenis"=> $request->id_jenis
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function destroy($id)
    {
        $query = BarangMerk::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
