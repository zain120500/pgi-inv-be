<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use Storage;
use Str;

class InternalMemoController extends Controller
{
    public function index()
    {
        $query = InternalMemo::orderBy('created_at', 'DESC')->paginate(15);

        return $this->successResponse($query,'Success', 200);
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
            "catatan"=> $request->catatan,
            "created_by"=> auth()->user()->id
        ]);

        $filenametostore ="";
        if(!empty($files)) {

            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
                $image = str_replace($replace, '', $image_64); 
                $image = str_replace(' ', '+', $image); 
                $imageName = Str::random(10).'.'.$extension;
                $filenametostore = Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

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
        $query->cabang->makeHidden(['created_at','updated_at']);
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
