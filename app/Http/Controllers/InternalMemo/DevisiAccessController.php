<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Devisi;
use App\Model\DevisiAccessFpp;


class DevisiAccessController extends Controller
{
    public function index()
    {
        $query = DevisiAccessFpp::paginate(15);
        return $this->successResponse($query,'Success', 200);
    }

    public function all()
    {
        $query = DevisiAccessFpp::all()->makeHidden(['created_at','updated_at']);
        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $datas = $request->data;
        $query = [];
        foreach ($datas as $key => $data) {

            $getData = DevisiAccessFpp::where([ 
                "devisi_id"=> $data['devisi_id']
            ])->delete();

            foreach ($data['id_kategori_fpp'] as $keys => $id_kategori) {
                $query[] = DevisiAccessFpp::create([
                    "id_kategori_fpp"=> $id_kategori,
                    "devisi_id"=> $data['devisi_id']
                ]);
            }
        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process failed', 403);
        }
    }

    public function show($id)
    {
        // if($query){
        //     return $this->successResponse($query,'Success', 200);
        // } else {
        //     return $this->errorResponse('Data is Null', 403);
        // }
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
        $query = DevisiAccessFpp::where([ 
            "devisi_id"=> $id
        ])->delete();

        if (!empty($query)) {
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }
}
