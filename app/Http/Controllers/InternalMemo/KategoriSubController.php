<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriSubFpp;


class KategoriSubController extends Controller
{

    public function index()
    {
        $query = KategoriSubFpp::paginate(15);

        return $this->successResponse($query,'Success', 200);

    }

    public function all()
    {
        $query = KategoriSubFpp::all();
        return $this->successResponse($query,'Success', 200);
    }


    public function store(Request $request)
    {
        $query = KategoriSubFpp::create([
            "name"=> $request->name,
            "id_kategori_jenis"=> $request->id_kategori_jenis,
            "sla" => $request->sla
        ]);

        if ($query) {
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function show($id)
    {
        $query = KategoriSubFpp::find($id);

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
        $query = KategoriSubFpp::where('id', $id)->update([
            "name"=> $request->name,
            "id_kategori_jenis"=> $request->id_kategori_jenis,
            "sla" => $request->sla
        ]);

        if ($query) {
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }


    public function destroy($id)
    {
        $query = KategoriSubFpp::find($id);

        if (!empty($query)) {
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
