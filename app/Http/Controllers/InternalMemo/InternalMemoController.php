<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\InternalMemoBarang;
use App\Model\InternalMemoMaintenance;
use App\Model\InternalMemoRating;
use App\Model\KategoriProsesPic;
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

        $internal = InternalMemo::where('flag', '!=', 4)
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($request->id_devisi) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_devisi', $request->id_devisi)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->id_kategori_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_fpp', $request->id_kategori_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->id_cabang) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_cabang', $request->id_cabang)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->id_kategori_jenis_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->id_kategori_sub_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->flag) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->where('flag', $request->flag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->created_at) {
            $internal = InternalMemo::orderBy('created_at', $request->created_at)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->startDate && $request->endDate) {
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $internal = InternalMemo::whereBetween('created_at', [$startDate, $endDate])
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->id_cabang_multiple) {
            $record = $request->id_cabang_multiple;
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $record)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
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
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')->whereHas('cabang', function ($query) use ($request) {
                $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
            })->withCount('memoMaintenanceCount', 'totalUserMaintenance')->get();
        }

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

        //        DB::beginTransaction();
        //
        //        try {

        $files = $request['files'];
        $videos = $request['videos'];

        $number = InternalMemo::count('id');

        $internalMemo = InternalMemo::create([
            "im_number" => "IM" . Carbon::now()->format('Ymd') . str_pad($number + 1, 3, 0, STR_PAD_LEFT),
            //            "id_kategori_fpp"=> $request->id_kategori_fpp,
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

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function show($id)
    {
        $query = InternalMemo::where('id', $id)->with('memoMaintenance.userMaintenance')->withCount('memoMaintenanceCount', 'totalUserMaintenance')->first();

        $now = date('Y-m-d H:i:s', strtotime('now'));

        $query->MemoFile->makeHidden(['created_at', 'updated_at']);
        $query->createdBy->makeHidden(['created_at', 'updated_at', 'email_verified_at']);
        $query->cabang;
        $query->devisi->makeHidden(['created_at', 'updated_at']);
        //        $query->kategori->makeHidden(['created_at','updated_at']);
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

        //        $query['barang'] = DB::table('internal_memo_barang')
        //            ->where('id_internal_memo', '=', $query->id)
        //            ->join("cabang", "cabang.id", "=", "internal_memo_barang.cabang_id")
        //            ->join("stok_barang",function($join){
        //                $join->on("stok_barang.id_tipe","=","internal_memo_barang.id_barang")
        //                    ->on("stok_barang.pic","=","cabang.kode");
        //            })
        //            ->join("barang_tipe","barang_tipe.id","=","stok_barang.id_tipe")
        //            ->select('internal_memo_barang.*','barang_tipe.tipe', 'stok_barang.jumlah_stok', 'stok_barang.nomer_barang', 'stok_barang.pic')
        //            ->get();
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

        //        DB::beginTransaction();
        //
        //        try {

        $query = InternalMemo::where('id', $id)->first();

        $query->update([
            //            "id_kategori_fpp"=> $request->id_kategori_fpp,
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


        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function updateFile(Request $request, $id)
    {

        //        DB::beginTransaction();
        //
        //        try {

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

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function addNewFile(Request $request, $id)
    {

        //        DB::beginTransaction();
        //
        //        try {

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

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function destroy($id)
    {

        //        DB::beginTransaction();
        //
        //        try {

        $imFile = InternalMemoFile::find($id);

        Storage::disk('sftp')->delete(basename($imFile->path));

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $imFile
        );

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function ignoreMemo(Request $request, $id)
    {

        //        DB::beginTransaction();
        //
        //        try {

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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function ignoreMemoAll(Request $request)
    {


        //        DB::beginTransaction();
        //
        //        try {

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

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
    }

    public function createInternalRating(Request $request, $id)
    {

        //        DB::beginTransaction();
        //
        //        try {

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

        //            DB::commit();
        //        } catch (\Throwable $e) {
        //            DB::rollback();
        //        }
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

        $record = InternalMemo::whereIn('flag', $archiveFlag)
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        if ($request->im_number) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('im_number', 'like', '%' . $request->im_number . '%')
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        } else if ($request->flag_status) {
            $record = InternalMemo::withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('flag', $request->flag_status)
                ->paginate(15);
        } elseif ($request->id_kategori_jenis_fpp) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->paginate(15);
        } elseif ($request->id_kategori_sub_fpp) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->paginate(15);
        } elseif ($request->id_devisi) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_devisi', $request->id_devisi)
                ->paginate(15);
        } elseif ($request->id_cabang) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_cabang', $request->id_cabang)
                ->paginate(15);
        } else if ($request->created_at) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', $request->created_at)
                ->paginate(15);
        } else if ($request->startDate && $request->endDate) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->paginate(15);
        } else if ($request->id_cabang_multiple) {
            $id_cabang_multiple = $request->id_cabang_multiple;
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $id_cabang_multiple)
                ->paginate(15);
        }

        $record->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang->kabupatenKota;
            $query->devisi;
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
        //        DB::beginTransaction();
        //        try {

        $internalMemo = InternalMemo::where('id', '=', $id)->first();

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        if ($pic->kategori_proses == 1) {
<<<<<<< HEAD
=======
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
>>>>>>> 12299c1 (barang masuk by cabang penerima)
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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );

        //            DB::commit();
        //        } catch (\Exception $e) {
        //            DB::rollback();
        //            return $e->getMessage();
        //        }
    }

    public function accMemoAll(Request $request)
    {

        //        DB::beginTransaction();
        //        try {

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

        //            DB::commit();
        //        } catch (\Exception $e) {
        //            DB::rollback();
        //            return $e->getMessage();
        //        }
    }

    public function paginateKuKc(Request $request)
    {
        $internal = InternalMemo::orderBy('created_at', 'DESC')
            ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->paginate(15);

        if ($request->id_devisi) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_devisi', $request->id_devisi)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->id_kategori_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_kategori_fpp', $request->id_kategori_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->id_cabang) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_cabang', $request->id_cabang)->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->id_kategori_jenis_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->id_kategori_sub_fpp) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->flag) {
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->where('flag', $request->flag)->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->created_at) {
            $internal = InternalMemo::orderBy('created_at', $request->created_at)
                ->where('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->get();
        } else if ($request->startDate && $request->endDate) {
            $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
            $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

            $internal = InternalMemo::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        } else if ($request->id_cabang_multiple) {
            $record = $request->id_cabang_multiple;
            $internal = InternalMemo::orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $this->cabangGlobal()->pluck('id'))
                ->whereIn('id_cabang', $record)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->paginate(15);
        }

        $collect = $internal->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang->kabupatenKota;
            $query->devisi;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        if ($request->kabupaten_kota_id) {
            $internal = InternalMemo::with('cabang.kabupatenKota', 'devisi', 'kategoriJenis', 'kategoriSub')
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
            $internal
        );
    }

    public function cancelMemo(Request $request, $id)
    {

        //        DB::beginTransaction();
        //        try {

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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );


        //            DB::commit();
        //        } catch (\Exception $e) {
        //            DB::rollback();
        //            return $e->getMessage();
        //        }
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

            if ($kProses == null) {
                $dashboardIm = InternalMemo::whereIn('id_cabang', $this->cabangGlobal()->pluck('kode'))->get();
                $val = 0;
            } else if ($kProses->kategori_proses == 1) {
                $val = $dashboardIm->whereIn('flag', [1])->count();
            } else if ($kProses->kategori_proses == 2 || $kProses->kategori_proses == 3) {
                $val = $dashboardIm->whereIn('flag', [2])->count();
            }

            $res = ([
                "belum_disetujui" => $dashboardIm->whereIn('flag', [0])->count(),
                "total_memo" => $dashboardIm->whereIn('flag', [1, 2, 3, 10, 4])->count(),
                "disetujui" => $val,
                "diproses" => $dashboardIm->whereIn('flag', [3])->count(),
                "ditolak" => $dashboardIm->whereIn('flag', [10])->count(),
                "diselesaikan" => $dashboardIm->whereIn('flag', [4])->count(),
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

        $record = InternalMemo::whereIn('flag', $archiveFlag)
            ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        if ($request->im_number) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->where('im_number', 'like', '%' . $request->im_number . '%')
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        } else if ($request->flag_status) {
            $record = InternalMemo::withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('flag', $request->flag_status)
                ->paginate(15);
        } elseif ($request->id_kategori_jenis_fpp) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_jenis_fpp', $request->id_kategori_jenis_fpp)
                ->paginate(15);
        } elseif ($request->id_kategori_sub_fpp) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_kategori_sub_fpp', $request->id_kategori_sub_fpp)
                ->paginate(15);
        } elseif ($request->id_devisi) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_devisi', $request->id_devisi)
                ->paginate(15);
        } elseif ($request->id_cabang) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('id_cabang', $request->id_cabang)
                ->paginate(15);
        } else if ($request->created_at) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', $request->created_at)
                ->paginate(15);
        } else if ($request->startDate && $request->endDate) {
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->paginate(15);
        } else if ($request->id_cabang_multiple) {
            $id_cabang_multiple = $request->id_cabang_multiple;
            $record = InternalMemo::whereIn('flag', $archiveFlag)
                ->withCount('memoMaintenanceCount', 'totalUserMaintenance')
                ->orderBy('created_at', 'DESC')
                ->whereIn('id_cabang', $id_cabang_multiple)
                ->paginate(15);
        }

        $record->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang->kabupatenKota;
            $query->devisi;
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
