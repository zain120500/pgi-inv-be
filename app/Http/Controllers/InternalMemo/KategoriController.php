<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = KategoriFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->kategoriJenis;
            return $query;
        });

        return $this->successResponse($kategori,'Success', 200);
    }

    public function all()
    {
        $query = KategoriFpp::all();
        return $this->successResponse($query,'Success', 200);
    }

    public function store(Request $request)
    {
        $query = KategoriFpp::create([
                    "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
                    "name"=> $request->name,
                    "sla" => $request->sla,
                    "created_by"=> auth()->user()->id,
                ]);
        
        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function show($id)
    {

        $query = KategoriFpp::find($id);

        if(!empty($query)){
            // $query->kategoriJenis;
            $query->kategoriSub;
            
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
        $query = KategoriFpp::where('id', $id)
                ->update([
                    "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
                    "name"=> $request->name,
                    "sla" => $request->sla,
                    "created_by"=> auth()->user()->id,
                ]);
        
        if ($query) {
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function destroy($id)
    {
        $query = KategoriFpp::find($id);

        if(!empty($query)){
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
