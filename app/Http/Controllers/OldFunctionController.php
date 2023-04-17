<?php

namespace App\Http\Controllers;

use App\Helpers\Constants;
use App\Model\BarangHistory;
use App\Model\BarangTipe;
use App\Model\Cabang;
use App\Model\HistoryMemo;
use App\Model\InternalMemo;
use App\Model\InternalMemoBarang;
use App\Model\InternalMemoFile;
use App\Model\InternalMemoMaintenance;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriPicFpp;
use App\Model\Pemakaian;
use App\Model\StokBarang;
use App\Model\UserMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

    /**
     * Endpoint Untuk Membuat Maintenance Menggunakan Id User Maintenance, Internal Memo
     */
    public function internalUserMaintenance(Request $request)
    {
        $userMaintenance[] = $request->id_user_maintenance;
        $internal[] = $request->id_memo;

        foreach ($internal[0] as $keys => $values){
            foreach ($userMaintenance[0] as $key => $value){
                $imMaintenance = InternalMemoMaintenance::create([
                    'id_internal_memo' => $values,
                    'id_user_maintenance' => $value,
                    'date' => Carbon::now(),
                    'created_by' => auth()->user()->id
                ]);
            }
        }

        if($imMaintenance){
            return $this->successResponse($imMaintenance,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    /**
     * Endpoint Untuk Membuat Maintenance Menggunakan Id Internal Memo, Barang
     */
    public function internalBarangMaintenance(Request $request)
    {
        $barang[] = $request->id_barang;
        $internal[] = $request->id_memo;

        foreach ($internal[0] as $key => $values){
            foreach ($barang[0] as $keys => $value){
                $imMaintenance = InternalMemoBarang::create([
                    'id_internal_memo' => $values,
                    'id_barang' => $value,
                    'created_by' => auth()->user()->id
                ]);
            }
        }

        if($imMaintenance){
            $this->whatsuppMessage();
            return $this->successResponse($imMaintenance,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    /**
     * Endpoint Untuk Membuat Maintenance Menggunakan Id User Maintenance, Internal Memo, Barang
     */
    public function internalMaintenance(Request $request)
    {
        $user[] = $request->id_user_maintenance;
        $iMemo[] = $request->id_memo;
        $barangs[] = $request->id_barang;
        $quantity = $request->quantity;
        $cabang = $request->cabang;

        foreach ($iMemo[0] as $key => $memos){
            $internalMemo = InternalMemo::where('id', $memos)->get()->pluck('id_cabang');

            $cab = Cabang::where('id', $internalMemo)->get()->pluck('kode');

            foreach ($user[0] as $keys => $users){
                $imMaintenance = InternalMemoMaintenance::create([
                    'id_internal_memo' => $memos,
                    'id_user_maintenance' => $users,
                    'date' => $request->date,
                    'link' => (Str::random(5).$users),
                    'kode' => (Str::random(5)),
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);

                $userMaintenance = UserMaintenance::where('id', $users)->first();
                $userMaintenance->update(['flag' => 1]); //update pic sedang bertugas
            }

            foreach ($barangs[0] as $i => $barang){
                $imBarang = InternalMemoBarang::create([
                    'id_internal_memo' => $memos,
                    'id_maintenance' => $imMaintenance->id,
                    'id_barang' => $barang,
                    'created_by' => auth()->user()->id
                ]);

                BarangHistory::create([
                    'id_barang_tipe' => $barang
                ]);

                if($cabang == !null){
                    if($quantity == !null) {
                        InternalMemoBarang::where('id_barang', $barang)->update([
                            'quantity' => $quantity[$i],
                            'cabang_id' => $cabang[$i]
                        ]);

                        $cabs = Cabang::where('id', $cabang[$i])->get()->pluck('kode');

                        foreach ($cabs as $ca) {
                            $stockBarang = StokBarang::where('id_tipe', $barang)->where('pic', $ca)->first();

                            Pemakaian::create([
                                'tanggal' => Carbon::now()->format('Y-m-d'),
                                'pic' => $stockBarang->pic,
                                'nomer_barang' => $stockBarang->nomer_barang,
                                'id_tipe' => $stockBarang->id_tipe,
                                'jumlah' => $quantity[$i],
                                'satuan' => $stockBarang->satuan,
                                'harga' => $stockBarang->total_asset,
                                'total_harga' => $stockBarang->total_asset,
                                'imei' => $stockBarang->imei,
                                'detail_barang' => $stockBarang->detail_barang,
                                'keperluan' => 'Kebutuhan Cabang',
                                'pemakai' => 'Cabang',
                                'user_input' => $stockBarang->user_input,
                                'last_update' => $stockBarang->last_update
                            ]);
                        }
                    }else{
                        return $this->errorResponse(Constants::ERROR_MESSAGE_9002, 403);
                    }
                }else{
                    if($quantity == !null) {
                        $c = Cabang::where('id', $internalMemo)->first();

                        InternalMemoBarang::where('id_barang', $barang)->update([
                            'quantity' => $quantity[$i]
                        ]);

                        InternalMemoBarang::where('id_internal_memo', $memos)->update([
                            'cabang_id' => $c->id
                        ]);

                        $stockBarangs = StokBarang::where('id_tipe', $barang)->where('pic', $cab)->first();

                        Pemakaian::create([
                            'tanggal' => Carbon::now()->format('Y-m-d'),
                            'pic' => $stockBarangs->pic,
                            'nomer_barang' => $stockBarangs->nomer_barang,
                            'id_tipe' => $stockBarangs->id_tipe,
                            'jumlah' => $quantity[$i],
                            'satuan' => $stockBarangs->satuan,
                            'harga' => $stockBarangs->total_asset,
                            'total_harga' => $stockBarangs->total_asset,
                            'imei' => $stockBarangs->imei,
                            'detail_barang' => $stockBarangs->detail_barang,
                            'keperluan' => 'Kebutuhan Cabang',
                            'pemakai' => 'Cabang',
                            'user_input' => $stockBarangs->user_input,
                            'last_update' => $stockBarangs->last_update
                        ]);
                    }else{
                        return $this->errorResponse(Constants::ERROR_MESSAGE_9002, 403);
                    }
                }
            }
            $this->whatsuppMessage($memos);
            $this->accMemoByPic($memos);
        }
        $this->createHistoryBarang($barangs[0]);

        if($imBarang){
            return $this->successResponse($imBarang,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function credelMemoMaintenance(Request $request)
    {
        $cIdMemo = $request->id_memo_create;
        $cIdUserMaintenance = $request->id_user_maintenance_create;
        $dIdMemo = $request->id_memo_del;
        $dIdUserMaintenance = $request->id_user_maintenance_del;

        $cIdBarang = $request->id_barang_create;
        $cCabang = $request->cabang_create;
        $dIdBarang = $request->id_barang_del;
        $dCabang = $request->cabang_del;
        $quantity = $request->quantity;

//        if(!empty($cIdMemo) && !empty($cIdUserMaintenance)) {
//            foreach ($cIdMemo as $keys => $values) {
//                foreach ($cIdUserMaintenance as $key => $value) {
//                    $user = InternalMemoMaintenance::where('id_internal_memo', $values)->where('id_user_maintenance', $value)->get()->pluck('id');
//                    $userDel = InternalMemoMaintenance::find($user);
//                    $userDel->each->delete();
//                }
//            }
//        }else{
//            return "Gagal";
//        }
//
//        if(!empty($dIdMemo) && !empty($dIdUserMaintenance)) {
//            foreach ($dIdMemo as $i => $val) {
//                foreach ($dIdUserMaintenance as $v => $vals) {
//                    InternalMemoMaintenance::create([
//                        'id_internal_memo' => $val,
//                        'id_user_maintenance' => $vals,
//                        'date' => Carbon::now(),
//                        'link' => 'asd',
//                        'kode' => 'asd',
//                        'flag' => 0,
//                        'created_by' => auth()->user()->id
//                    ]);
//                }
//            }
//        }else{
//            return "Gagal";
//        }
//
//        if(!empty($dIdBarang) && !empty($dCabang)) {
//            foreach ($dIdBarang as $keys => $values) {
//                foreach ($dCabang as $key => $value) {
//                    $barang = InternalMemoBarang::where('id_barang', $dIdBarang)->where('cabang_id', $value)->get()->pluck('id');
//                    $barangDel = InternalMemoBarang::find($barang);
//                    $barangDel->each->delete();
//                }
//            }
//        }else{
//            return "Gagal";
//        }

        if(!empty($cIdBarang) && !empty($cCabang)) {
            foreach ($cIdBarang as $is => $val) {
                foreach ($cCabang as $iv => $vals) {
                    InternalMemoBarang::create([
                        'id_internal_memo' => 9,
                        'id_barang' => $val,
                        'quantity' => $quantity[$iv],
                        'cabang_id' => $vals,
                        'created_by' => auth()->user()->id
                    ]);
                }
            }
        }else{
            return "Gagal";
        }

        return "Success";
    }

    public function testCronJob()
    {
        $imMaintenance = InternalMemoMaintenance::get();

        $arr = [];
        $arrs = [];
        foreach ($imMaintenance as $keys => $value){

            if($value->date < Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at)->addDays(1)->format('Y-m-d'))
            {
                $value->update([
                    'flag' => 10,
                ]);
                $arr[] = $imMaintenance->first();
            }else {
                $arr[] = "Gagal";
            }

            $iMemo = InternalMemo::where('id', $value->id_internal_memo)->get();
            if(!empty($iMemo)) {
                foreach ($iMemo as $key => $memo) {
                    $memo->update([
                        'flag' => 10,
                    ]);
                    $arrs[] = $memo->first();
                }
            }
        }
    }

    public function getStockBarang()
    {
        $record = BarangTipe::orderBy('id', 'DESC')->paginate(20);

        $record->map(function ($query){
            $query->stockBarang;

            return $query;
        });

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
    /**
     * Whatsupp Pesan Saat Membuat Tugas Maintenance
     */
    public function whatsuppMessage($memos)
    {
        $test[] = $memos;
        $user = array();
        foreach ($test as $key => $value){
            $memo = InternalMemo::where('id', $value)->first();
            $kjFpp = KategoriJenisFpp::where('id', $memo->id_kategori_jenis_fpp)->first();
            $cabang = Cabang::where('id', $memo->id_cabang)->first();
            $maintenanceUser = InternalMemoMaintenance::where(['id_internal_memo' =>  $memo->id])->get();

            foreach ($maintenanceUser as $keys => $values){
                $user = UserMaintenance::where('id', $values->id_user_maintenance)->first();
                $this->ProceesWaCabang($memo, $cabang, $user, $values, $kjFpp);
                $this->ProceesWaMaintenance($memo, $cabang, $user, $values);
            }


        }
    }

    /**
     * Function untuk whatsupp cabang
     */
    public function  ProceesWaCabang($memo, $cabang, $user, $values, $kjFpp) {
        $token = env("FONTE_TOKEN");
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $cabang->telepon,
                'message' => "
    No Memo : *$memo->im_number*
    Kategori : *$kjFpp->name*
    Status : *PROSES*
    Cabang : *$cabang->name*
    Alamat : *$cabang->alamat*
    Maintenance : *$user->nama*
    No Telp Maintenance : *$user->no_telp*
    Tanggal Pekerjaan : *$values->date*
    Kode Maintenance : *$values->kode*
    ",
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    /**
     * Function untuk whatsupp maintenance
     */
    public function  ProceesWaMaintenance($memo, $cabang, $user, $values) {
        $token = env("FONTE_TOKEN");
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $user->no_telp,
                'message' => "
No Memo : *$memo->im_number*
Status : *PROSES*
Cabang : *$cabang->name*
Alamat : *$cabang->alamat*
Telp Cabang : *$cabang->telepon*
Maintenance : *$user->nama*
Tanggal Pekerjaan : *$values->date*
Link : http://portal.pusatgadai.id/konfirmasi-kehadiran/$values->link
Maps : https://maps.google.com/?q=$cabang->latitude,$cabang->longitude
                ",
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

}
