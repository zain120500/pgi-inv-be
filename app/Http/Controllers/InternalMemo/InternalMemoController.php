<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\InternalMemoMaintenance;
use App\Model\InternalMemoRating;
use App\Model\StokBarang;
use App\Model\UserMaintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Model\KategoriPicFpp;
use App\Model\InternalMemo;
use App\Model\InternalMemoFile;
use App\Model\HistoryMemo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Storage;
use Str;
use DateTime;

class InternalMemoController extends Controller
{
    public function index(Request $request)
    {
        $internal = InternalMemo::where('flag', '!=', 4)->orderBy('created_at', 'DESC')->get();

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
            $query->cabang->kabupatenKota;
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if($request->kabupaten_kota_id) {
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->get();
        }

        if($internal){
            return $this->successResponse($internal,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function create()
    {
        return getkodeinvoice();
    }

    public function store(Request $request)
    {
        $files = $request['files'];
        $videos = $request['videos'];

        $number = InternalMemo::count('id');

        $internalMemo = InternalMemo::create([
            "im_number" => "IM". Carbon::now()->format('Ymd') . str_pad($number+1, 3, 0, STR_PAD_LEFT),
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
            "keterangan"=> "Internal memo baru dibuat oleh".' '.auth()->user()->name
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
                    "flag" => "foto"
                ]);
            }
        }
        if(!empty($videos)){
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                $video = InternalMemoFile::create([
                    "id_internal_memo" => $internalMemo->id,
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        if($internalMemo){
            return $this->successResponse($internalMemo,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function show($id)
    {
        $query = InternalMemo::where('id', $id)->with('memoMaintenance.userMaintenance')->first();

        $now = date('Y-m-d H:i:s', strtotime('now'));

        $query->MemoFile->makeHidden(['created_at','updated_at']);
        $query->createdBy->makeHidden(['created_at','updated_at','email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at','updated_at']);
        $query->kategori->makeHidden(['created_at','updated_at']);
        $query->kategoriJenis->makeHidden(['created_at','updated_at']);
        $query->kategoriSub;
        $query->memoRating;
        $internalMemoBarang = $query->internalMemoBarang;
//        foreach ($internalMemoBarang as $keys => $values){
//            $cabs = Cabang::where('id', $query->cabang->id)->first();
//            $query->arr = StokBarang::where('id_tipe', $values->id_barang)->where('pic', $cabs->kode)->get();
//        }
        $listHistoryMemo = $query->listHistoryMemo;
        $time_before = new DateTime($now);
        foreach ($listHistoryMemo as $key => $value) {

            if($key == 0){
                $value['waktu_proses'] = "00:00";
                $time_before = new DateTime($value->created_at);
            } else {
                $time_after = new DateTime($value->created_at);
                $interval = $time_before->diff($time_after);
                $value['waktu_proses'] = $interval->format('%H:%i');
                $time_before = new DateTime($value->created_at);
            }
        }

        if($query){
            return $this->successResponse($query,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
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
            return $this->successResponse($query,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function updateFile(Request $request, $id)
    {
        $files = $request['files'];
        $videos = $request['videos'];

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
                    "flag" => "foto"
                ]);
            }
        }
        if(!empty($videos)){
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                $query->update([
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        if($query){
            return $this->successResponse($query,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function addNewFile(Request $request, $id)
    {
        $files = $request['files'];
        $videos = $request['videos'];

        $internal = InternalMemo::where('id', $id)->first();

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
                    "id_internal_memo" => $internal->id,
                    "path" => $imageName,
                    "flag" => "foto"
                ]);
            }
        }
        if(!empty($videos)){
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                InternalMemoFile::create([
                    "id_internal_memo" => $internal->id,
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        if($internal){
            return $this->successResponse($internal,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function destroy($id)
    {
        $imFile = InternalMemoFile::find($id);

        Storage::disk('sftp')->delete(basename($imFile->path));
        if(!empty($imFile)){
            $imFile->delete();
            return $this->successResponse($imFile,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function ignoreMemo(Request $request,$id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses == 1 || $pic->kategori_proses == 2){
            if(!empty($request->catatan_tolak)){
                $internalMemo->update([
                    'catatan_tolak' => $request->catatan_tolak,
                    'flag' => 10
                ]);
            }else{
                $internalMemo->update([
                    'flag' => 10
                ]);
            }
        }

        $create = HistoryMemo::create([
            "id_internal_memo"=> $internalMemo->id,
            "user_id"=> auth()->user()->id,
            "status"=> 10,
            "keterangan"=> $this->getFlagStatus(10).' '.auth()->user()->name,
            "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
            "waktu" => Carbon::now()->format('h')
        ]);

        if($create){
            return $this->successResponse($create,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function ignoreMemoAll(Request $request)
    {
        $ids[] =  $request->id;
        $array = [];

        foreach ($ids[0] as $key => $value){
            $array[] = InternalMemo::where('id', $value)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            if($pic->kategori_proses == 1 || $pic->kategori_proses == 2){
                if(!empty($request->catatan_tolak)){
                    InternalMemo::where('id', $value)->update([
                        'catatan_tolak' => $request->catatan_tolak,
                        'flag' => 10
                    ]);
                }else{
                    InternalMemo::where('id', $value)->update([
                        'flag' => 10
                    ]);
                }
            }

            $create = HistoryMemo::create([
                "id_internal_memo"=> $value,
                "user_id"=> auth()->user()->id,
                "status"=> 10,
                "keterangan"=> $this->getFlagStatus(10).' '.auth()->user()->name,
                "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                "waktu" => Carbon::now()->format('h')
            ]);

        }

        if($array){
            return $this->successResponse($array,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function createInternalRating(Request $request, $id)
    {
        $internal = InternalMemo::find($id);

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses == 0|| $pic->kategori_proses == 4){
            $create = InternalMemoRating::create([
                'id_internal_memo' => $internal->id,
                'user_id' => auth()->user()->id,
                'rating' => $request->rating,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->user()->id,
            ]);
        }else{
            return $this->errorResponse(Constants::ERROR_MESSAGE_9001, 403);
        }

        if($create){
            return $this->successResponse($create,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getRating($id)
    {
        $rating = InternalMemoRating::where('id_internal_memo', $id)->get();

        $collect = $rating->map(function ($query) {
            $query->internalMemo;

            return $query;
        });

        if($rating){
            return $this->successResponse($rating,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getMemoMaintenance($id)
    {
        $internal = InternalMemoMaintenance::where('id_internal_memo', $id)->get();

        $collect = $internal->map(function ($query) {
            $query->internalMemo;

            return $query;
        });

        if($internal){
            return $this->successResponse($internal,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    /**
     * TESTING FUNCTION FOR NEXT PHASE
     */
    public function uploadBuktiPic(Request $request, $id)
    {
        $files = $request['files'];
        $videos = $request['videos'];

        $userMaintenance = InternalMemoMaintenance::where('id_internal_memo', $id)->get()->pluck('id_user_maintenance');
        foreach ($userMaintenance as $key => $value){
            UserMaintenance::where('id', $value)->update([
                'flag' => 0
            ]);
        }

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        /**
         * Cek Kategori Ku / Pic
         */
        if($pic->kategori_proses === 0 || $pic->kategori_proses === 4){
            InternalMemo::where('id', $id)->update([
                'flag' => $pic->kategori_proses
            ]);

            HistoryMemo::create([
                "id_internal_memo"=> $id,
                "user_id"=> auth()->user()->id,
                "status"=> $pic->kategori_proses,
                "keterangan"=> $this->getFlagStatus($pic->kategori_proses).' '.auth()->user()->name
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
                        "flag" => "foto_pic_ku"
                    ]);
                }
            }
            if(!empty($videos)){
                foreach ($videos as $key => $video) {
                    $image_64 = $video; //your base64 encoded data
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                    $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $videoName = Str::random(10).'.'.$extension;
                    Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                    $video = InternalMemoFile::create([
                        "id_internal_memo" => $id,
                        "path" => $videoName,
                        "flag" => "video_pic_ku"
                    ]);
                }
            }
        }else{
            return $this->errorResponse(Constants::ERROR_MESSAGE_9001, 403);
        }

        if($pic){
            return $this->successResponse($pic,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function pdfLetter($id)
    {
        $query = InternalMemo::find($id);

        $query->MemoFile->makeHidden(['created_at','updated_at']);
        $query->createdBy->makeHidden(['created_at','updated_at','email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at','updated_at']);
        $query->kategoriJenis->makeHidden(['created_at','updated_at']);
        $query->kategoriSub;
        $query->listHistoryMemo;
        $query->memoMaintenance;

        return view('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo]);
    }

    public function pdfMemo($id)
    {
        $query = InternalMemo::find($id);

        $query->MemoFile->makeHidden(['created_at','updated_at']);
        $query->createdBy->makeHidden(['created_at','updated_at','email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at','updated_at']);
        $query->kategoriJenis->makeHidden(['created_at','updated_at']);
        $query->kategoriSub;
        $query->listHistoryMemo;
        $query->memoMaintenance;

        $customPaper = array(360,360,360,360);
        $pdf = PDF::loadView('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo])->setPaper('a4');
        return $pdf->download('internal-memo');
    }

    public function menuArchive(Request $request)
    {
        $record = InternalMemo::whereIn('flag', [4, 3, 10])->orderBy('created_at', 'DESC')->paginate(15);

        if($request->im_number){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('im_number', $request->im_number)->orderBy('created_at', 'DESC')->paginate(15);
        }elseif($request->id_kategori_fpp){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('id_kategori_fpp', $request->id_kategori_fpp)->orderBy('created_at', 'DESC')->paginate(15);
        }elseif($request->id_kategori_jenis_fpp){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)->orderBy('created_at', 'DESC')->paginate(15);
        }elseif($request->id_kategori_sub_fpp){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)->orderBy('created_at', 'DESC')->paginate(15);
        }elseif($request->id_devisi){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('id_devisi', $request->id_devisi)->orderBy('created_at', 'DESC')->paginate(15);
        }elseif($request->id_cabang){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->where('id_cabang', $request->id_cabang)->orderBy('created_at', 'DESC')->paginate(15);
        }else if($request->created_at){
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->orderBy('created_at', $request->created_at)->paginate(15);
        }else if($request->startDate && $request->endDate){
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $record = InternalMemo::where('flag', 4)->where('flag', 3)->whereBetween('created_at', [$startDate, $endDate])->paginate(15);
        }else if($request->flag) {
            $record = InternalMemo::orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)->get();
        }else if($request->id_cabang_multiple) {
            $id_cabang_multiple = $request->id_cabang_multiple;
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $id_cabang_multiple)->paginate(15);
        }

        $collect = $record->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang->kabupatenKota;
            $query->devisi;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if($request->kabupaten_kota_id) {
            $record = InternalMemo::whereIn('flag', [4, 3, 10])->with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->paginate(15);
        }

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function accMemo($id){
        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if($pic->kategori_proses == 1 || $pic->kategori_proses == 2){
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

            if($pic->kategori_proses == 1 || $pic->kategori_proses == 2){
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

    public function paginateKuKc(Request $request)
    {
        $user = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if ($user->kategori_proses == 0 || $user->kategori_proses == 4){

            $internal = InternalMemo::orderBy('created_at', 'DESC')->paginate(15);

            if($request->id_devisi){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('id_devisi', $request->id_devisi)->paginate(15);
            }else if($request->id_kategori_fpp){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('id_kategori_fpp', $request->id_kategori_fpp)->paginate(15);
            }else if($request->id_cabang){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('id_cabang', $request->id_cabang)->paginate(15);
            }else if($request->id_kategori_jenis_fpp){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)->paginate(15);
            }else if($request->id_kategori_sub_fpp){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)->paginate(15);
            }else if($request->flag){
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->where('flag', $request->flag)->paginate(15);
            }else if($request->created_at){
                $internal = InternalMemo::orderBy('created_at', $request->created_at)->get();
            }else if($request->startDate && $request->endDate){
                $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
                $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

                $internal = InternalMemo::whereBetween('created_at', [$startDate, $endDate])->paginate(15);
            }else if($request->id_cabang_multiple) {
                $record = $request->id_cabang_multiple;
                $internal = InternalMemo::orderBy('created_at', 'DESC')
                    ->whereIn('id_cabang', $record)->paginate(15);
            }

            $collect = $internal->map(function ($query) {
                $query['flag_status'] = $this->getFlagStatus($query->flag);
                $query->cabang->kabupatenKota;
                $query->devisi;
                $query->kategoriJenis;
                $query->kategoriSub;

                return $query;
            });

            if($request->kabupaten_kota_id) {
                $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function($query) use ($request) {
                    $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
                })->paginate(15);
            }
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_9001, 403);
        }

        if($internal){
            return $this->successResponse($internal,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function testIndexMemo(Request $request)
    {
        $internal = InternalMemo::where('flag', '!=', 4)->orderBy('created_at', 'DESC')->get();

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
        }else if($request->flag == 0){
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)->get();
        }else if($request->created_at){
            $internal = InternalMemo::orderBy('created_at', $request->created_at)->get();
        }else if($request->flag) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)->get();
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
            $query->cabang->kabupatenKota;
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if($request->kabupaten_kota_id) {
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->get();
        }

        if($internal){
            return $this->successResponse($internal,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
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
            return "Disetujui";
        } else if($id == 3){
            return "Diproses";
        } else if($id == 4){
            return "Diselesaikan";
        } else if($id == 5){
            return "Dikonfirmasi";
        } else if($id == 6){
            return "Selesai";
        } else if($id == 7){
            return "Request Batal";
        } else if($id == 8){
            return "Batal";
        }else if($id == 9){
            return "Dihapus";
        } else if($id == 10){
            return "Ditolak";
        } else if($id == 11){
            return "Reschedule";
        }
    }

}
