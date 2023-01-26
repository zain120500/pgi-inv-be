<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;


class KategoriJenisController extends Controller
{

    public function index()
    {
        $kategori = KategoriJenisFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->kategori;
            return $query;
        });

        return $this->successResponse($kategori,'Success', 200);
    }

    public function all()
    {
        $query = KategoriJenisFpp::all();

        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = KategoriJenisFpp::create([
                    "name"=> $request->name
                ]);
        
        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function show($id)
    {
        $query = KategoriJenisFpp::find($id);

        if(!empty($query)){
            $query->kategori;

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
        $query = KategoriJenisFpp::where('id', $id)
                ->update([
                    "name"=> $request->name
                ]);
        
        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }


    public function destroy($id)
    {
        $query = KategoriJenisFpp::find($id);

        if(!empty($query)){
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
