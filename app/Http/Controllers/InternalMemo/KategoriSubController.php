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
        $query = KategoriSubFpp::pluck('name')->toArray();

        return $this->successResponse($query,'Success', 200);
    }


    public function store(Request $request)
    {
        //
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
        //
    }


    public function destroy($id)
    {
        //
    }
}
