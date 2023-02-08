<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriPicFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use App\Model\HistoryMemo;
use Illuminate\Support\Arr;
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
            $query->kategoriJenis;
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
        $query->listHistoryMemo;

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

    public function accMemo(Request $request, $id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();
        $historyMemo = HistoryMemo::where('id_internal_memo', '=', $internalMemo->id)->first();

        $create = HistoryMemo::create([
            "id_internal_memo"=> $historyMemo->id_internal_memo,
            "user_id"=> auth()->user()->id,
            "status"=> $internalMemo->flag,
            "keterangan"=> $this->getFlagStatus($internalMemo->flag). " Di Acc Oleh ". auth()->user()->name
        ]);

        if($create){
            return $this->successResponse($create,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function dropdownKategoriFpp(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->where('id_kategori_fpp', $request->id_kategori_fpp)->get();

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function dropdownJenisKategoriFpp(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)->get();

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function dropdownSubKategoriFpp(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)->get();

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function dropdownDivisi(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->where('id_devisi', $request->id_devisi)->get();

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function dropdownCabang(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->where('id_cabang', $request->id_cabang)->get();

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function ascDesc(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', $request->param)->get();

        return $this->successResponse($internal,'Success', 200);
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
