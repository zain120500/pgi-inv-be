<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;


class InternalMemoController extends Controller
{
    public function index()
    {
        $query = InternalMemo::paginate(15);

        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = InternalMemo::create([
            "id_kategori_fpp"=> $request->id_kategori_fpp,
            "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
            "id_kategori_sub_fpp"=> $request->id_kategori_sub_fpp,
            "id_devisi"=> $request->id_devisi,
            "qty"=> $request->qty,
            "catatan"=> $request->catatan,
            "created_by"=> auth()->user()->id
        ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function show($id)
    {
        $query = InternalMemo::find($id);
        $query->MemoFile->makeHidden(['created_at','updated_at']);
        $query->createdBy->makeHidden(['created_at','updated_at','email_verified_at']);
        $query->devisi->makeHidden(['created_at','updated_at']);
        $query->kategoriJenis->kategori->makeHidden(['created_at','updated_at']);
        $query->kategoriSub;

        return $this->successResponse($query,'Success', 200);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = InternalMemo::where('id', $id)
            ->update([
                "id_kategori_fpp"=> $request->id_kategori_fpp,
                "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
                "id_kategori_sub_fpp"=> $request->id_kategori_sub_fpp,
                "id_devisi"=> $request->id_devisi,
                "qty"=> $request->qty,
                "catatan"=> $request->catatan,
                "created_by"=> auth()->user()->id
            ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function destroy($id)
    {
        $query = InternalMemo::find($id);

        if(!empty($query)){
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
