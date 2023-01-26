<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Kategori;

class KategoriController extends Controller
{

    public function index()
    {
        $kategori = Kategori::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query['barangJenis'] = $query->barangJenis;

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $kategori->setCollection($collect)
        ], 200);  
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Kategori::create([
            "nama"=> $request->nama,
            "kode"=> $request->kode
        ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function show($id)
    {
        $query = Kategori::find($id);
        
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
        $query = Kategori::where('id', $id)
            ->update([
            "nama"=> $request->nama,
            "kode"=> $request->kode
        ]);

        return $this->successResponse($query,'Success', 200);
    }

    public function destroy($id)
    {
        $query = Kategori::find($id)->delete();
        return $this->successResponse($query,'Success', 200);
    }
}
