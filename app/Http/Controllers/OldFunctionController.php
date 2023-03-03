<?php

namespace App\Http\Controllers;

use App\Helpers\Constants;
use App\Model\HistoryMemo;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use App\Model\InternalMemoMaintenance;
use App\Model\KategoriPicFpp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OldFunctionController extends Controller
{

    public function uploadBuktiPic(Request $request, $id)
    {
        $files = $request['files'];

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses == 0 || $pic->kategori_proses == 2){
            InternalMemo::where('id', $id)->update([
                'flag' => 3
            ]);

            HistoryMemo::create([
                "id_internal_memo"=> $id,
                "user_id"=> auth()->user()->id,
                "status"=> 3,
                "keterangan"=> $this->getFlagStatus(3).' '.auth()->user()->name
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

                    $file = InternalMemoFile::create([
                        "id_internal_memo" => $id,
                        "path" => $imageName,
                        "flag" => 1
                    ]);
                }

            }
        }else{
            return $this->errorResponse(Constants::ERROR_MESSAGE_9000, 403);
        }

        if($file){
            return $this->successResponse($file,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function accMemo($id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        $history = HistoryMemo::where('id_internal_memo', $id)->count('status');

        $history = HistoryMemo::where('id_internal_memo', $id)->whereIn('status', [0, 1])->count();

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
            "keterangan"=> $this->getFlagStatus($pic->kategori_proses).' '.auth()->user()->name
        ]);

        if($create){
            return $this->successResponse($create,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function accMemoAll(Request $request)
    {
        $ids[] =  $request->id;
        $array = [];

        foreach ($ids[0] as $key => $value){
            $array[] = InternalMemo::where('id', $value)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            $history = HistoryMemo::where('id_internal_memo', $value)->whereIn('status', [0, 1])->count();

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
                "keterangan"=> $this->getFlagStatus($pic->kategori_proses).' '.auth()->user()->name
            ]);

        }

        if($array){
            return $this->successResponse($array,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
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
        $internalMemo[] = $id;

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        foreach ($internalMemo as $key => $value) {
            $memo = InternalMemo::where('id', $value)->first();

            if($pic->kategori_proses === 3) {
                InternalMemo::where('id', $memo->id)->update([
                    'flag' => $pic->kategori_proses
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $memo->id,
                    "user_id" => auth()->user()->id,
                    "status" => 2,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
                ]);
            }
        }

        if($create){
            return $this->successResponse($create,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
}
