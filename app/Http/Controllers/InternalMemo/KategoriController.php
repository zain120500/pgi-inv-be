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
        $query = KategoriFpp::pluck('name')->toArray();

        return $this->successResponse($query,'Success', 200);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {

        $query = KategoriFpp::find($id);

        if(!empty($query)){
            $query->kategoriJenis;
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
        //
    }

    public function destroy($id)
    {
        //
    }
}
