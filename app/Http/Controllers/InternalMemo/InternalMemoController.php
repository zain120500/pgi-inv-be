<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\HistoryMemo;
use App\Model\InternalMemo;
use App\Model\InternalMemoBarang;
use App\Model\InternalMemoFile;
use App\Model\InternalMemoMaintenance;
use App\Model\InternalMemoRating;
use App\Model\InternalMemoVendor;
use App\Model\Kabupaten;
use App\Model\KategoriPicFpp;
use App\Model\KategoriProsesPic;
use App\Model\StokBarang;
use App\Model\UserMaintenance;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Storage;
use Str;

class InternalMemoController extends Controller
{
    public function index(Request $request)
    {
        $internal = InternalMemo::query();

        if ($request->id_devisi) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('id_devisi', $request->id_devisi)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_kategori_fpp) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('id_kategori_fpp', $request->id_kategori_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_cabang) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('id_cabang', $request->id_cabang)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_kategori_jenis_fpp) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_kategori_sub_fpp) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->flag) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->created_at) {
            $internal = $internal->orderBy('created_at', $request->created_at)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->startDate && $request->endDate) {
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $internal = $internal->whereBetween('created_at', [$startDate, $endDate])
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_cabang_multiple) {
            $record = $request->id_cabang_multiple;
            $internal = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $record)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->created_by) {
            $internal = $internal->orderBy('created_at', 'DESC')
                ->where('created_by', $request->created_by)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        }

        $internal = $internal->get();

        $internal->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = DB::table('cabang')
                ->join("kabupaten_kota", "cabang.kabupaten_kota_id", "=", "kabupaten_kota.id")
                ->select('cabang.*', 'kabupaten_kota.name as kabupaten_name')
                ->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if ($request->kabupaten_kota_id) {
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function ($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->withCount('memoMaintenanceCount', 'totalUserMaintenance')->get();
        }

//        if ($request->kc_kcs) {
//            $internal = InternalMemo::whereHas('createdBy.userStaffCabang', function ($query) use ($request) {
//                $query->where('user_staff_id', $request->kc_kcs);
//            })
//                ->with('createdBy.userStaffCabang', 'cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')
//                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')->get();
//        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
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

        DB::beginTransaction();

        try {

            $internalMemo = InternalMemo::create([
                "im_number" => "IM" . Carbon::now()->format('Ymd') . str_pad($number + 1, 3, 0, STR_PAD_LEFT),
                // "id_kategori_fpp"=> $request->id_kategori_fpp,
                "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
                "id_kategori_sub_fpp" => $request->id_kategori_sub_fpp,
                "id_devisi" => $request->id_devisi,
                "id_cabang" => $request->id_cabang,
                "qty" => $request->qty,
                "flag" => 0,
                "catatan" => $request->catatan,
                "created_by" => auth()->user()->id
            ]);

            HistoryMemo::create([
                "id_internal_memo" => $internalMemo->id,
                "user_id" => auth()->user()->id,
                "status" => 0,
                "keterangan" => "Internal memo baru dibuat oleh" . ' ' . auth()->user()->name
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
        }

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                InternalMemoFile::create([
                    "id_internal_memo" => $internalMemo->id,
                    "path" => $imageName,
                    "flag" => "foto"
                ]);
            }
        }
        if (!empty($videos)) {
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                $video = InternalMemoFile::create([
                    "id_internal_memo" => $internalMemo->id,
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internalMemo
        );
    }

    public function show($id)
    {
        $query = InternalMemo::where('id', $id)->with('memoMaintenance.userMaintenance')->withCount('memoMaintenanceCount', 'totalUserMaintenance')->first();

        if($query->vendor_type == 0 || $query->vendor_type == null){
            $now = date('Y-m-d H:i:s', strtotime('now'));

            $query->MemoFile->makeHidden(['created_at', 'updated_at']);
            $query->createdBy->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
            $query->cabang;
            $query->devisi->makeHidden(['created_at', 'updated_at']);
            // $query->kategori->makeHidden(['created_at','updated_at']);
            $query->kategoriJenis->makeHidden(['created_at', 'updated_at']);
            $query->kategoriSub;
            $query->memoRating;
            $listHistoryMemo = $query->listHistoryMemo;
            $time_before = new DateTime($now);
            $barang_memo = InternalMemoBarang::where('id_internal_memo', $query->id)->get();

            if ($barang_memo->isEmpty()) {
                $query['barang'] = "";
            } else {
                foreach ($barang_memo as $b) {
                    $cabang = Cabang::where('id', $b->cabang_id)->first();
                    $value[] = DB::table('internal_memo_barang')
                        ->join("stok_barang", "stok_barang.id_tipe", "=", "internal_memo_barang.id_barang")
                        ->where('stok_barang.id_tipe', $b->id_barang)
                        ->where('stok_barang.pic', $cabang->kode)
                        ->join("barang_tipe", "barang_tipe.id", "=", "stok_barang.id_tipe")
                        ->select('internal_memo_barang.*', 'stok_barang.*', 'barang_tipe.*')
                        ->first();
                }

                $query['barang'] = $value;
            }

            foreach ($listHistoryMemo as $key => $value) {

                if ($key == 0) {
                    $value['waktu_proses'] = "00:00";
                    $time_before = new DateTime($value->created_at);
                } else {
                    $time_after = new DateTime($value->created_at);
                    $interval = $time_before->diff($time_after);
                    $value['waktu_proses'] = $interval->format('%H:%i');
                    $time_before = new DateTime($value->created_at);
                }
            }

            $decode = json_decode($query, true);

            $userMaintenanceArray = [];

            // Menggabungkan user maintenance menjadi satu
            foreach ($decode['memo_maintenance'] as $key => $mm) {
                if (count($mm['user_maintenance']) > 0) {
                    $userMaintenanceArray[$key] = $mm['user_maintenance'][0]['id'];
                }
            }

            // sort arraynya
            sort($userMaintenanceArray);
            $sortArray = array_values($userMaintenanceArray);

            $userMaintenanceArrayUser = [];

            // menemukan user berdasarkan id
            foreach ($sortArray as $key => $sa) {
                $userMaintenance = UserMaintenance::where("id", $sa)->first();
                $userMaintenanceArrayUser[$key] = $userMaintenance;
            }

            $query['user_maintenance'] = $userMaintenanceArrayUser;
        }else if($query->vendor_type == 1){
            $now = date('Y-m-d H:i:s', strtotime('now'));

            $query->MemoFile->makeHidden(['created_at', 'updated_at']);
            $query->createdBy->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
            $query->cabang;
            $query->devisi->makeHidden(['created_at', 'updated_at']);
            // $query->kategori->makeHidden(['created_at','updated_at']);
            $query->kategoriJenis->makeHidden(['created_at', 'updated_at']);
            $query->kategoriSub;
            $query->memoRating;
            $query->internalMemoVendor;
            $listHistoryMemo = $query->listHistoryMemo;
            $time_before = new DateTime($now);

            foreach ($listHistoryMemo as $key => $value) {

                if ($key == 0) {
                    $value['waktu_proses'] = "00:00";
                    $time_before = new DateTime($value->created_at);
                } else {
                    $time_after = new DateTime($value->created_at);
                    $interval = $time_before->diff($time_after);
                    $value['waktu_proses'] = $interval->format('%H:%i');
                    $time_before = new DateTime($value->created_at);
                }
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {

        $query = InternalMemo::where('id', $id)->first();

        $query->update([
            //"id_kategori_fpp"=> $request->id_kategori_fpp,
            "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
            "id_kategori_sub_fpp" => $request->id_kategori_sub_fpp,
            "id_devisi" => $request->id_devisi,
            "id_cabang" => $request->id_cabang,
            "qty" => $request->qty,
            "catatan" => $request->catatan,
            "created_by" => auth()->user()->id
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function updateFile(Request $request, $id)
    {


        $files = $request['files'];
        $videos = $request['videos'];

        $query = InternalMemoFile::where('id', $id)->first();

        if (!empty($files)) {

            foreach ($files as $key => $file) {

                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $query->update([
                    "path" => $imageName,
                    "flag" => "foto"
                ]);
            }
        }
        if (!empty($videos)) {
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                $query->update([
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function addNewFile(Request $request, $id)
    {


        $files = $request['files'];
        $videos = $request['videos'];

        $internal = InternalMemo::where('id', $id)->first();

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                InternalMemoFile::create([
                    "id_internal_memo" => $internal->id,
                    "path" => $imageName,
                    "flag" => "foto"
                ]);
            }
        }
        if (!empty($videos)) {
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                InternalMemoFile::create([
                    "id_internal_memo" => $internal->id,
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
    }

    public function destroy($id)
    {



        $imFile = InternalMemoFile::find($id);

        Storage::disk('sftp')->delete(basename($imFile->path));

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $imFile
        );
    }

    public function ignoreMemo(Request $request, $id)
    {


        DB::beginTransaction();
        try {

            $internalMemo = InternalMemo::where('id', '=', $id)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            if ($pic->kategori_proses == 1) {
                $internalMemo->update([
                    'flag' => 10
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus(10) . ' ' . auth()->user()->name
                ]);
            } else if ($pic->kategori_proses == 2) {
                InternalMemo::where('id', $id)->update([
                    'flag' => 10
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus(10) . ' ' . auth()->user()->name
                ]);
            }

            $create = HistoryMemo::create([
                "id_internal_memo" => $internalMemo->id,
                "user_id" => auth()->user()->id,
                "status" => 10,
                "keterangan" => $this->getFlagStatus(10) . ' ' . auth()->user()->name,
                "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                "waktu" => Carbon::now()->format('h')
            ]);

            DB::commit();

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $create
            );
        } catch (\Throwable $e) {
            DB::rollback();
        }
    }

    public function ignoreMemoAll(Request $request)
    {


        $ids[] =  $request->id;
        $array = [];

        foreach ($ids[0] as $key => $value) {
            $array[] = InternalMemo::where('id', $value)->first();

            if (!empty($request->catatan_tolak)) {
                InternalMemo::where('id', $value)->update([
                    'catatan_tolak' => $request->catatan_tolak,
                    'flag' => 10
                ]);
            } else {
                InternalMemo::where('id', $value)->update([
                    'flag' => 10
                ]);
            }

            $create = HistoryMemo::create([
                "id_internal_memo" => $value,
                "user_id" => auth()->user()->id,
                "status" => 10,
                "keterangan" => $this->getFlagStatus(10) . ' ' . auth()->user()->name,
                "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                "waktu" => Carbon::now()->format('h')
            ]);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $array
        );
    }

    public function createInternalRating(Request $request, $id)
    {

        $internal = InternalMemo::find($id);

        $create = InternalMemoRating::create([
            'id_internal_memo' => $internal->id,
            'user_id' => auth()->user()->id,
            'rating' => $request->rating,
            'keterangan' => $request->keterangan,
            'created_by' => auth()->user()->id,
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );
    }

    public function getRating($id)
    {
        $rating = InternalMemoRating::where('id_internal_memo', $id)->get();

        $collect = $rating->map(function ($query) {
            $query->internalMemo;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $rating
        );
    }

    public function getMemoMaintenance($id)
    {
        $internal = InternalMemoMaintenance::where('id_internal_memo', $id)->get();

        $collect = $internal->map(function ($query) {
            $query->internalMemo;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
    }

    /**
     * TESTING FUNCTION FOR NEXT PHASE
     */
    public function uploadBuktiPic(Request $request, $id)
    {
        $files = $request['files'];
        $videos = $request['videos'];

        //        DB::beginTransaction();
        //        try {
        $im = InternalMemo::where('id', $id)->first();
        if($im->vendor_type == 0){
            $userMaintenance = InternalMemoMaintenance::where('id_internal_memo', $id)->get()->pluck('id_user_maintenance');
            foreach ($userMaintenance as $key => $value) {
                UserMaintenance::where('id', $value)->update([
                    'flag' => 0
                ]);
            }

            InternalMemoMaintenance::where('id_internal_memo', $id)->update([
                'flag' => 4
            ]);

            $memo = InternalMemo::where('id', $id)->update([
                'flag' => 4
            ]);

            HistoryMemo::create([
                "id_internal_memo" => $id,
                "user_id" => auth()->user()->id,
                "status" => 4,
                "keterangan" => $this->getFlagStatus(4) . ' ' . auth()->user()->name
            ]);
        }else if($im->vendor_type == 1){
            InternalMemoVendor::where('id_internal_memo', $id)->update([
                'flag' => 1
            ]);

            $memo = InternalMemo::where('id', $id)->update([
                'flag' => 4
            ]);

            HistoryMemo::create([
                "id_internal_memo" => $id,
                "user_id" => auth()->user()->id,
                "status" => 4,
                "keterangan" => $this->getFlagStatus(4) . ' ' . auth()->user()->name
            ]);
        }

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $file = InternalMemoFile::create([
                    "id_internal_memo" => $id,
                    "path" => $imageName,
                    "flag" => "foto_pic_ku"
                ]);
            }
        }
        if (!empty($videos)) {
            foreach ($videos as $key => $video) {
                $image_64 = $video; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $videoName = Str::random(10) . '.' . $extension;
                Storage::disk('sftp')->put($videoName, base64_decode(($image), 'r+'));

                $video = InternalMemoFile::create([
                    "id_internal_memo" => $id,
                    "path" => $videoName,
                    "flag" => "video_pic_ku"
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $memo
        );

        //            DB::commit();
        //        } catch (\Exception $e) {
        //            DB::rollback();
        //            return $e->getMessage();
        //        }
    }

    public function pdfLetter($id)
    {
        $query = InternalMemo::find($id);

        $query->MemoFile->makeHidden(['created_at', 'updated_at']);
        $query->createdBy->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at', 'updated_at']);
        $query->kategoriJenis->makeHidden(['created_at', 'updated_at']);
        $query->kategoriSub;
        $query->listHistoryMemo;
        $query->memoMaintenance;

        return view('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo]);
    }

    public function pdfMemo($id)
    {
        $query = InternalMemo::find($id);

        $query->MemoFile->makeHidden(['created_at', 'updated_at']);
        $query->createdBy->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at', 'updated_at']);
        $query->kategoriJenis->makeHidden(['created_at', 'updated_at']);
        $query->kategoriSub;
        $query->listHistoryMemo;
        $query->memoMaintenance;

        $customPaper = array(360, 360, 360, 360);
        $pdf = PDF::loadView('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo])->setPaper('a4');
        return $pdf->download('internal-memo');
    }

    public function menuArchive(Request $request)
    {
        $archiveFlag = [4, 3, 8, 7, 10, 12, 13];
        $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
        $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

        $internal = InternalMemo::query();

        $record = $internal->whereIn('flag', $archiveFlag)
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->orderBy('created_at', 'DESC');

        if ($request->im_number) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('im_number', 'like', '%' . $request->im_number . '%')
                ->orderBy('created_at', 'DESC');
        } else if ($request->flag_status && $request->startDate) {
            $record = $internal->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('flag', $request->flag_status);
        } else if ($request->flag_status) {
            $record = $internal->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('flag', $request->flag_status);
        } elseif ($request->id_kategori_jenis_fpp && $request->startDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp);
        } elseif ($request->id_kategori_jenis_fpp) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp);
        } elseif ($request->id_kategori_sub_fpp && $request->startDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp);
        } elseif ($request->id_kategori_sub_fpp) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp);
        } elseif ($request->id_devisi && $request->startDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_devisi', $request->id_devisi);
        } elseif ($request->id_devisi) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('id_devisi', $request->id_devisi);
        } elseif ($request->id_cabang && $request->startDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_cabang', $request->id_cabang);
        } elseif ($request->id_cabang) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('id_cabang', $request->id_cabang);
        } else if ($request->created_at) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', $request->created_at);
        } else if ($request->startDate && $request->endDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate]);
        } else if ($request->id_cabang_multiple) {
            $id_cabang_multiple = $request->id_cabang_multiple;
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $id_cabang_multiple);
        } else if ($request->created_by) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->where('created_by', $request->created_by)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        }

        $record = $record->paginate(15);

        $record->getCollection()->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = DB::table('cabang')
                ->join("kabupaten_kota", "cabang.kabupaten_kota_id", "=", "kabupaten_kota.id")
                ->select('cabang.*', 'kabupaten_kota.name as kabupaten_name')
                ->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if ($request->kabupaten_kota_id) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function ($query) use ($request) {
                    $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
                })->paginate(15);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function accMemo($id, Request $request)
    {
        DB::beginTransaction();
        try {

            $internalMemo = InternalMemo::where('id', '=', $id)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            if ($pic->kategori_proses == 1) {
                InternalMemo::where('id', $id)->update([
                    'flag' => $pic->kategori_proses
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
                ]);
            } else if ($pic->kategori_proses == 2) {

                InternalMemo::where('id', $id)->update([
                    'flag' => $pic->kategori_proses
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
                ]);
            } else if ($pic->kategori_proses == 2) {
                InternalMemo::where('id', $id)->update([
                    'flag' => $pic->kategori_proses
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $internalMemo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "catatan" => $request->catatan,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return self::buildResponse(
                Constants::HTTP_CODE_500,
                Constants::ERROR_MESSAGE_500,
                $e->getMessage()
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            'Berhasil Acc Memo'
        );
    }

    public function accMemoAll(Request $request)
    {

        $ids[] =  $request->id;
        $array = [];

        foreach ($ids[0] as $key => $value) {
            $array[] = InternalMemo::where('id', $value)->first();

            $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

            if ($pic->kategori_proses == 1 || $pic->kategori_proses == 2) {
                InternalMemo::where('id', $value)->update([
                    'flag' => $pic->kategori_proses
                ]);
            }

            $create = HistoryMemo::create([
                "id_internal_memo" => $value,
                "user_id" => auth()->user()->id,
                "status" => $pic->kategori_proses,
                "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name
            ]);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $array
        );
    }

    public function paginateKuKc(Request $request)
    {
        $internal = InternalMemo::query();

        $record = $internal->orderBy('created_at', 'DESC')
            ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance');

        if ($request->id_devisi) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_devisi', $request->id_devisi)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_cabang) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_cabang', $request->id_cabang)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_kategori_jenis_fpp) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_kategori_sub_fpp) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->flag) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('flag', $request->flag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->created_at) {
            $record = $internal->orderBy('created_at', $request->created_at)
                ->where('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->startDate && $request->endDate) {
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $record = $internal->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->id_cabang_multiple) {
            $cabang = $request->id_cabang_multiple;
            $record = $internal->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->whereIn('id_cabang', $cabang)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        } else if ($request->created_by) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->where('created_by', $request->created_by)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        }

        $record = $record->paginate(15);

        $record->getCollection()->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = DB::table('cabang')
                ->join("kabupaten_kota", "cabang.kabupaten_kota_id", "=", "kabupaten_kota.id")
                ->select('cabang.*', 'kabupaten_kota.name as kabupaten_name')
                ->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if ($request->kabupaten_kota_id) {
            $record = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')
                ->whereHas('cabang', function ($query) use ($request) {
                    $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
                })
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function cancelMemo(Request $request, $id)
    {

        DB::beginTransaction();
        try {

            $internalMemo = InternalMemo::where('id', '=', $id)->first();

            if (!empty($request->catatan_tolak)) {
                $internalMemo->update([
                    'flag' => 8
                ]);
            } else {
                $internalMemo->update([
                    'flag' => 8
                ]);
            }

            $create = HistoryMemo::create([
                "id_internal_memo" => $internalMemo->id,
                "user_id" => auth()->user()->id,
                "status" => 8,
                "keterangan" => $this->getFlagStatus(8) . ' ' . auth()->user()->name,
                "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                "waktu" => Carbon::now()->format('h')
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );
    }

    public function imByCabangId(Request $request)
    {
        $user = auth()->user()->id;
        $cabang = Cabang::where('area_manager_id', $user)
            ->orWhere('kepala_cabang_senior_id', $user)
            ->orWhere('kepala_cabang_id', $user)
            ->orWhere('kepala_unit_id', $user)->get()->pluck('id');

        $internal = InternalMemo::whereIn('id_cabang', $cabang)->where('flag', '!=', 4)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')->get();

        if ($request->id_devisi) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('id_devisi', $request->id_devisi)->get();
        } else if ($request->id_kategori_fpp) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('id_kategori_fpp', $request->id_kategori_fpp)->get();
        } else if ($request->id_cabang) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('id_cabang', $request->id_cabang)->get();
        } else if ($request->id_kategori_jenis_fpp) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)->get();
        } else if ($request->id_kategori_sub_fpp) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)->get();
        } else if ($request->flag) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)->get();
        } else if ($request->created_at) {
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', $request->created_at)->get();
        } else if ($request->startDate && $request->endDate) {
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->whereBetween('created_at', [$startDate, $endDate])->get();
        } else if ($request->id_cabang_multiple) {
            $record = $request->id_cabang_multiple;
            $internal = InternalMemo::whereIn('id_cabang', $cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')->orderBy('created_at', 'DESC')
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

        if ($request->kabupaten_kota_id) {
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->withCount('memoMaintenanceCount', 'totalUserMaintenance')->whereHas('cabang', function ($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->get();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
    }

    public function dashboardKcImStatus()
    {

        try {

            $kProses = KategoriPicFpp::where('user_id', auth()->user()->id)->first();
            $dashboardIm = InternalMemo::get();
            $dashboardKc = InternalMemo::whereIn('id_cabang', $this->cabangGlobal()->pluck('kode'))->get();

            if ($kProses == null) {
                $total_memo = 0;
                $val = 0;
                $belum_disetujui = 0;
            } else if ($kProses->kategori_proses == 1) {
                $val = $dashboardIm->whereIn('flag', [1])->count();
                $belum_disetujui = $dashboardIm->whereIn('flag', [0])->count();
                $total_memo = $dashboardIm->whereIn('flag', [0, 1])->count();
            } else if ($kProses->kategori_proses == 2) {
                $val = $dashboardIm->whereIn('flag', [2])->count();
                $belum_disetujui = $dashboardIm->whereIn('flag', [1])->count();
                $total_memo = $dashboardIm->whereIn('flag', [2, 1])->count();
            } else if ($kProses->kategori_proses == 3) {
                $val = 0;
                $belum_disetujui = $dashboardIm->whereIn('flag', [2])->count();
                $total_memo = $dashboardIm->whereIn('flag', [2])->count();
            }

            $res = ([
                "belum_disetujui_gm_pic" => $belum_disetujui,
                "total_memo_gm_pic" => $total_memo,
                "disetujui_gm_pic" => $val,

                "diproses_kc" => $dashboardKc->whereIn('flag', [3])->count(),
                "ditolak_kc" => $dashboardKc->whereIn('flag', [10])->count(),
                "diselesaikan_kc" => $dashboardKc->whereIn('flag', [4])->count(),
                "belum_disetujui_kc" => $dashboardKc->whereIn('flag', [0])->count(),
                "total_memo_kc" => $dashboardKc->count(),
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $res
        );
    }

    public function dashboardMtImStatus()
    {
        try {
            $uMaintenance = UserMaintenance::where('user_id', auth()->user()->id)->first();
            $iMemoMaintenance = InternalMemoMaintenance::where('id_user_maintenance', $uMaintenance->id)->get()->pluck('id_internal_memo');
            $iMemo = InternalMemo::whereIn('id', $iMemoMaintenance)->get();

            $res = ([
                "total_memo" => $iMemo->whereIn('flag', [3, 4])->count(),
                "diproses" => $iMemo->where('flag', 3)->count(),
                "diselesaikan" => $iMemo->where('flag', 4)->count(),
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $res
        );
    }

    public function menuArchivePic(Request $request)
    {
        $archiveFlag = [4, 11];
        $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
        $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

        $internal = InternalMemo::query();

        $record = $internal->whereIn('flag', $archiveFlag)
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->orderBy('created_at', 'DESC');

        if ($request->im_number) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('im_number', 'like', '%' . $request->im_number . '%')
                ->orderBy('created_at', 'DESC');
        } else if ($request->flag_status) {
            $record = $internal->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('flag', $request->flag_status);
        } elseif ($request->id_kategori_jenis_fpp) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp);
        } elseif ($request->id_kategori_sub_fpp) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp);
        } elseif ($request->id_devisi) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_devisi', $request->id_devisi);
        } elseif ($request->id_cabang) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_cabang', $request->id_cabang);
        } else if ($request->created_at) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', $request->created_at);
        } else if ($request->startDate && $request->endDate) {
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate]);
        } else if ($request->id_cabang_multiple) {
            $id_cabang_multiple = $request->id_cabang_multiple;
            $record = $internal->whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $id_cabang_multiple);
        } else if ($request->created_by) {
            $record = $internal->orderBy('created_at', 'DESC')
                ->where('created_by', $request->created_by)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance');
        }

        $record = $record->paginate(15);

        $record->getCollection()->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = DB::table('cabang')
                ->join("kabupaten_kota", "cabang.kabupaten_kota_id", "=", "kabupaten_kota.id")
                ->select('cabang.*', 'kabupaten_kota.name as kabupaten_name')
                ->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if ($request->kabupaten_kota_id) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->with('cabang.kabupatenKota')
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function ($query) use ($request) {
                    $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
                })->paginate(15);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function store2(Request $request)
    {

        $files = $request['files'];
        $videos = $request['videos'];

        $number = InternalMemo::count('id');

        DB::beginTransaction();

        try {

            $internalMemo = InternalMemo::create([
                "im_number" => "IM" . Carbon::now()->format('Ymd') . str_pad($number + 1, 3, 0, STR_PAD_LEFT),
                // "id_kategori_fpp"=> $request->id_kategori_fpp,
                "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
                "id_kategori_sub_fpp" => $request->id_kategori_sub_fpp,
                "id_devisi" => $request->id_devisi,
                "id_cabang" => $request->id_cabang,
                "qty" => $request->qty,
                "flag" => 0,
                "catatan" => $request->catatan,
                "created_by" => auth()->user()->id
            ]);

            HistoryMemo::create([
                "id_internal_memo" => $internalMemo->id,
                "user_id" => auth()->user()->id,
                "status" => 0,
                "keterangan" => "Internal memo baru dibuat oleh" . ' ' . auth()->user()->name
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
        }

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $imageName = Str::random(10). '.' .time().'.'.$request->file->extension();
                Storage::disk('sftp')->put($imageName, $file);

                InternalMemoFile::create([
                    "id_internal_memo" => $internalMemo->id,
                    "path" => $imageName,
                    "flag" => "foto"
                ]);
            }
        }
        if (!empty($videos)) {
            foreach ($videos as $key => $video) {
                $videoName = Str::random(10). '.' .time().'.'.$request->$video->extension();
                Storage::disk('sftp')->put($videoName, $video);

                InternalMemoFile::create([
                    "id_internal_memo" => $internalMemo->id,
                    "path" => $videoName,
                    "flag" => "video"
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internalMemo
        );
    }

    //1. disetujui, 2.diproses, 3. diselesaikan, 4.dikonfirmasi, 5.selesai, 6.request batal, 7.batal, 10.dihapus
    public function getFlagStatus($id)
    {
        if ($id == 0) {
            return "Ditinjau Ulang";
        } else if ($id == 1) {
            return "Disetujui";
        } else if ($id == 2) {
            return "Disetujui";
        } else if ($id == 3) {
            return "Diproses";
        } else if ($id == 4) {
            return "Diselesaikan";
        } else if ($id == 5) {
            return "Dikonfirmasi";
        } else if ($id == 6) {
            return "Selesai";
        } else if ($id == 7) {
            return "Request Batal";
        } else if ($id == 8) {
            return "Batal";
        } else if ($id == 9) {
            return "Dihapus";
        } else if ($id == 10) {
            return "Ditolak";
        } else if ($id == 11) {
            return "Reschedule";
        }
    }
}
