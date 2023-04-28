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
        $kategori = KategoriPicFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->user->makeHidden(['created_at','updated_at']);
            $query->kategori->kategoriJenis;
            $query->devisi->makeHidden(['created_at','updated_at']);
            return $query;
        });

        return $this->successResponse($kategori,'Success', 200);
    }

    public function all()
    {
        $kategori = KategoriPicFpp::get();

        $collect = $kategori->map(function ($query) {
            $query->user->makeHidden(['created_at','updated_at']);
            $query->kategori->kategoriJenis;
            $query->devisi->makeHidden(['created_at','updated_at']);
            return $query;
        });

        return $this->successResponse($kategori,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $kategori = $request->id_kategori_fpp;
        $kProses = $request->kategori_proses;

        try {
            $kModel = KategoriFpp::where('id', $kategori)->first();

            foreach ($kProses as $keys => $kP){
                $query = KategoriPicFpp::create([
                    "user_id"=> $request->user_id,
                    "devisi_id"=> $request->devisi_id,
                    "id_kategori_fpp"=> $kModel->id,
                    "id_kategori_jenis_fpp"=> $kModel->id_kategori_jenis_fpp,
                    "kategori_proses"=> $kP,
                    "created_by"=> auth()->user()->id
                ]);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

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
            $query->user->makeHidden(['created_at','updated_at']);
            $query->kategori->kategoriJenis;
            $query->devisi->makeHidden(['created_at','updated_at']);
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
        $query = KategoriPicFpp::find($id);

        if($query){
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
