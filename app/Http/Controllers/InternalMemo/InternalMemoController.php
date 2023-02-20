<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\User;
use App\Model\InternalMemoMaintenance;
use App\Model\InternalMemoRating;
use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriPicFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use App\Model\HistoryMemo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use Str;

class InternalMemoController extends Controller
{
    public function index(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')->get();
        if($request->id_devisi){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_devisi', $request->id_devisi)->get();
        }else if($request->id_kategori_fpp){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_fpp', $request->id_kategori_fpp)->get();
        }else if($request->id_cabang){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_cabang', $request->id_cabang)->get();
        }else if($request->id_kategori_jenis_fpp){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)->get();
        }else if($request->id_kategori_sub_fpp){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)->get();
        }else if($request->flag){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)->get();
        }else if($request->created_at){
            $internal = InternalMemo::orderBy('created_at', $request->created_at)->get();
        }else if($request->startDate && $request->endDate){
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $internal = InternalMemo::whereBetween('created_at', [$startDate, $endDate])->get();
        }else if($request->id_cabang_multiple) {
            $record = $request->id_cabang_multiple;
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $record)->get();
        }

        $collect = $internal->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang;
            $query->devisi;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if($internal){
            return $this->successResponse($internal,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function create()
    {
        return getkodeinvoice();
    }

    public function store(Request $request)
    {
        $files = $request['files'];

        $number = InternalMemo::count('id');

        $internalMemo = InternalMemo::create([
            "im_number" => "IM". Carbon::now()->format('Ymd') . str_pad($number+1, 4, 0, STR_PAD_LEFT),
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
                    "path" => $imageName,
                    "flag" => 0
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
        $query->kategoriJenis->makeHidden(['created_at','updated_at']);
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
        $query = InternalMemo::where('id', $id)->first();

        $query->update([
            "id_kategori_fpp"=> $request->id_kategori_fpp,
            "id_kategori_jenis_fpp"=> $request->id_kategori_jenis_fpp,
            "id_kategori_sub_fpp"=> $request->id_kategori_sub_fpp,
            "id_devisi"=> $request->id_devisi,
            "id_cabang"=> $request->id_cabang,
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

    public function updateFile(Request $request, $id)
    {
        $files = $request['files'];

        $query = InternalMemoFile::where('id', $id)->first();

        if(!empty($files)) {

            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $query->update([
                    "path" => $imageName,
                ]);
            }

        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function addNewFile(Request $request, $id)
    {
        $files = $request['files'];

        $query = InternalMemoFile::where('id', $id)->first();

        if(!empty($files)) {

            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $query->create([
                    "path" => $imageName,
                ]);
            }

        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function destroy($id)
    {
        $imFile = InternalMemoFile::find($id);

        Storage::disk('sftp')->delete(basename($imFile->path));
        if(!empty($imFile)){
            $imFile->delete();
            return $this->successResponse($imFile,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function accMemo($id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        $history = HistoryMemo::where('id_internal_memo', $id)->count('status');

        $countPic =  KategoriPicFpp::where(['kategori_proses' => 1, 'id_kategori_jenis_fpp' => 2])->count('id');

        if($countPic == $history){
            InternalMemo::where('id', $id)->update([
                'flag' => $pic->kategori_proses
            ]);
        }

        $create = HistoryMemo::create([
            "id_internal_memo"=> $internalMemo->id,
            "user_id"=> auth()->user()->id,
            "status"=> $pic->kategori_proses,
            "keterangan"=> $this->getFlagStatus($pic->kategori_proses). " Di Acc Oleh ". auth()->user()->name
        ]);

        if($create){
            return $this->successResponse($create,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function accMemoAll(Request $request)
    {
        $ids[] =  $request->id;
        $array = [];

        foreach ($ids[0] as $key => $value){
            $array[] = InternalMemo::where('id', $value)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            $history = HistoryMemo::where('id_internal_memo', $value)->count('status');

            $countPic =  KategoriPicFpp::where(['kategori_proses' => 1, 'id_kategori_jenis_fpp' => 2])->count('id');

            if($countPic == $history){
                InternalMemo::where('id', $value)->update([
                    'flag' => $pic->kategori_proses
                ]);
            }

            $create = HistoryMemo::create([
                "id_internal_memo"=> $value,
                "user_id"=> auth()->user()->id,
                "status"=> $pic->kategori_proses,
                "keterangan"=> $this->getFlagStatus($pic->kategori_proses). " Di Acc Oleh ". auth()->user()->name
            ]);

        }

        if($array){
            return $this->successResponse($array,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function ignoreMemo($id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $internalMemo->update([
            'flag' => 0
        ]);

        $create = HistoryMemo::create([
            "id_internal_memo"=> $internalMemo->id,
            "user_id"=> auth()->user()->id,
            "status"=> 11,
            "keterangan"=> $this->getFlagStatus(11). " Di Acc Oleh ". auth()->user()->name
        ]);

        if($create){
            return $this->successResponse($create,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function createInternalRating(Request $request, $id)
    {
        $internal = InternalMemo::find($id);

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses === 2){
            $create = InternalMemoRating::create([
                'id_internal_memo' => $internal->id,
                'user_id' => auth()->user()->id,
                'rating' => $request->rating,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->user()->id,
            ]);
        }else{
            return $this->errorResponse('Anda Bukan Pic', 403);
        }

        if($create){
            return $this->successResponse($create,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function getRating($id)
    {
        $internal = InternalMemo::find($id);

        $rating = InternalMemoRating::where('id_internal_memo', $internal->id)->get();

        $collect = $rating->map(function ($query) {
            $query->internalMemo;

            return $query;
        });

        if($rating){
            return $this->successResponse($rating,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function uploadBuktiPic(Request $request, $id)
    {
        $files = $request['files'];

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses == 0 || $pic->kategori_proses == 2){
            $query = InternalMemoFile::where('id', $id)->first();

            InternalMemo::where('id', $query->id)->update([
                'flag' => 3
            ]);

            HistoryMemo::create([
                "id_internal_memo"=> $query->id,
                "user_id"=> auth()->user()->id,
                "status"=> 3,
                "keterangan"=> $this->getFlagStatus(3). " Di Acc Oleh ". auth()->user()->name
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

                    $query->create([
                        "id_internal_memo" => $query->id,
                        "path" => $imageName,
                        "flag" => 1
                    ]);
                }

            }
        }else{
            return $this->errorResponse('Anda Bukan Pic', 403);
        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function createMemoMaintenance(Request $request, $id)
    {
        $interenal = InternalMemo::find($id);

        $imMaintenance = InternalMemoMaintenance::create([
            'id_internal_memo' => $interenal->id,
            'id_user_maintenance' => $request->id_user_maintenance,
            'date' => Carbon::now(),
            'created_by' => auth()->user()->id
        ]);

        if($imMaintenance){
            $this->accMemoByPic($interenal->id);
            return $this->successResponse($imMaintenance,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function accMemoByPic($id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses === 2) {
            InternalMemo::where('id', $id)->update([
                'flag' => 2
            ]);

            $memo = InternalMemo::where('id', '=', $id)->first();

            $create = HistoryMemo::create([
                "id_internal_memo"=> $internalMemo->id,
                "user_id"=> auth()->user()->id,
                "status"=> 1,
                "keterangan"=> $this->getFlagStatus($memo->flag). " Di Acc Oleh ". auth()->user()->name
            ]);
        }

        if($create){
            return $this->successResponse($create,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    //1. disetujui, 2.diproses, 3. diselesaikan, 4.dikonfirmasi, 5.selesai, 6.request batal, 7.batal, 10.dihapus
    public function getFlagStatus($id)
    {
        if($id == 0){
            return "Ditinjau Ulang";
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
        } else if($id == 11){
            return "DiTolak";
        }
    }

}
