<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Devisi;
use App\Model\DevisiAccessFpp;
use App\User;
use App\Model\KategoriPicFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriFpp;


class KategoriPicController extends Controller
{
    public function index()
    {
        $query = KategoriPicFpp::paginate(15);
        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = KategoriPicFpp::create([
            "user_id"=> $request->user_id,
            "devisi_id"=> $request->devisi_id,
            "id_kategori_fpp"=> $request->id_kategori_fpp,
            "created_by"=> auth()->user()->id
        ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function show($id)
    {
        $query = KategoriPicFpp::find($id);
        
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
        $query = KategoriPicFpp::where('id', $id)
            ->update([
                "user_id"=> $request->user_id,
                "devisi_id"=> $request->devisi_id,
                "id_kategori_fpp"=> $request->id_kategori_fpp,
                "created_by"=> auth()->user()->id
            ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroy($id)
    {
        $query = KategoriPicFpp::find($id)->delete();
        
        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
