<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriPicFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use App\Model\HistoryMemo;
use Storage;
use Str;

class InternalMemoController extends Controller
{
    public function index()
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->get();

        $collect = $internal->map(function ($query) {
            $query->cabang;
            $query->devisi;
            $query->kategoriJenis->kategori;
            $query->kategoriSub;

            return $query;
        });

        return $this->successResponse($internal,'Success', 200);
    }

    public function create()
    {
        return getkodeinvoice();
    }

    public function store(Request $request)
    {
        $files = $request['files'];

        $internalMemo = InternalMemo::create([
            "id_kategori_fpp"=> $request->id_kategori_fpp,
            "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
            "id_kategori_sub_fpp"=> $request->id_kategori_sub_fpp,
            "id_devisi"=> $request->id_devisi,
            "id_cabang"=> $request->id_cabang,
            "qty"=> $request->qty,
            "flag" => 0,
            "catatan"=> $request->catatan,
            "created_by"=> auth()->user()->id
        ]);

        HistoryMemo::create([
            "id_internal_memo"=> $internalMemo->id,
            "user_id"=> auth()->user()->id,
            "status"=> 0,
            "keterangan"=> "Internal memo baru dibuat oleh ". auth()->user()->name
        ]);

        if(!empty($files)) {

            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
                $image = str_replace($replace, '', $image_64); 
                $image = str_replace(' ', '+', $image); 
                $imageName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                InternalMemoFile::create([
                    "id_internal_memo"=> $internalMemo->id,
                    "path" => $imageName
                ]);
            }
            
        }
    
        if($internalMemo){
            return $this->successResponse($internalMemo,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function show($id)
    {
        $query = InternalMemo::find($id);
        $query->MemoFile->makeHidden(['created_at','updated_at']);
        $query->createdBy->makeHidden(['created_at','updated_at','email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at','updated_at']);
        $query->kategoriJenis->kategori->makeHidden(['created_at','updated_at']);
        $query->kategoriSub;
        $query->historyMemo;

        return $this->successResponse($query,'Success', 200);
    }

    public function accMemo(Request $request){

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

    //1. disetujui, 2.diproses, 3. diselesaikan, 4.dikonfirmasi, 5.selesai, 6.request batal, 7.batal, 10.dihapus	
    public function getFlagStatus($id)
    {
        if($id == 0){
            return "Pending";
        } else if($id == 1){
            return "Disetujui";
        } else if($id == 2){
            return "DiProses";
        } else if($id == 3){
            return "DiSelesaikan";
        } else if($id == 4){
            return "DiKonfirmasi";
        } else if($id == 5){
            return "Selesai";
        } else if($id == 6){
            return "Request Batal";
        } else if($id == 7){
            return "Batal";
        } else if($id == 10){
            return "DiHapus";
        }
    }
}
