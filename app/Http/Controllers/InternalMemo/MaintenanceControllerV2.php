<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangHistory;
use App\Model\BarangKeluar;
use App\Model\BarangMerk;
use App\Model\BarangTipe;
use App\Model\Cabang;
use App\Model\HistoryMemo;
use App\Model\InternalMemo;
use App\Model\InternalMemoBarang;
use App\Model\InternalMemoMaintenance;
use App\Model\KategoriPicFpp;
use App\Model\Pemakaian;
use App\Model\StokBarang;
use App\Model\SuratTugas;
use App\Model\UserMaintenance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MaintenanceControllerV2 extends Controller
{
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

        $number = SuratTugas::count('id');
        $sTugas = SuratTugas::create([
            'kode' => "ST". Carbon::now()->format('Ymd') . str_pad($number+1, 3, 0, STR_PAD_LEFT),
            'created_by' => auth()->user()->id
        ]);

        foreach ($iMemo[0] as $key => $memos){
            $internalMemo = InternalMemo::where('id', $memos)->get()->pluck('id_cabang');

            $cab = Cabang::where('id', $internalMemo)->get()->pluck('kode');

            foreach ($user[0] as $keys => $users){
                $imMaintenance = InternalMemoMaintenance::create([
                    'id_surat_tugas' => $sTugas->id,
                    'id_internal_memo' => $memos,
                    'id_user_maintenance' => $users,
                    'date' => Carbon::now(),
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

    public function createHistoryBarang($id)
    {
        $barang[] = $id;

        foreach ($barang[0] as $key => $value){
            $tipe = StokBarang::where('id_tipe', $value)->first();

            BarangHistory::create([
                'id_barang_tipe' => $value
            ]);
        }
    }

    /**
     * Whatsupp Pesan Saat Membuat Tugas Maintenance
     */
    public function whatsuppMessage($id)
    {
        $test[] = $id;
        $user = array();
        foreach ($test as $key => $value){
            $memo = InternalMemo::where('id', $value)->first();
            $cabang = Cabang::where('id', $memo->id_cabang)->first();
            $maintenanceUser = InternalMemoMaintenance::where(['id_internal_memo' =>  $memo->id])->get()->pluck('id_user_maintenance');
            $imUser = InternalMemoMaintenance::where(['id_internal_memo' =>  $memo->id])->first();

            foreach ($maintenanceUser as $values){
                $user = UserMaintenance::where('id', $values)->first();
                $this->ProceesWaCabang($memo, $cabang, $user, $imUser);
                $this->ProceesWaMaintenance($memo, $cabang, $user, $imUser);
            }
        }
    }

    /**
     * Function untuk whatsupp
     */
    public function  ProceesWaCabang($memo, $cabang, $user, $imUser) {
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
    Status : *PROSES*
    Cabang : *$cabang->name*
    Alamat : *$cabang->alamat*
    Maintenance : *$user->nama*
    No Telp Maintenance : *$user->no_telp*
    Tanggal Pekerjaan : *$user->created_at*
    Kode Maintenance : *$imUser->kode*
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
     * Function untuk whatsupp
     */
    public function  ProceesWaMaintenance($memo, $cabang, $user, $imUser) {
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
No Telp Maintenance : *$user->no_telp*
Tanggal Pekerjaan : *$user->created_at*
Link : https://portal.pusatgadai.id/$imUser->link
Maps : https://maps.google.com/?q=$cabang->latitude,$cabang->longitude
url : http://localhost:8000/api/internal-memo/memo/webhookTest
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

    public function accMemoByPic($id){
        $internalMemo[] = $id;

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        foreach ($internalMemo as $key => $value) {
            $memo = InternalMemo::where('id', $value)->first();

            if($pic->kategori_proses === 3) {
                $internal = InternalMemo::where('id', $memo->id)->update([
                    'flag' => $pic->kategori_proses
                ]);

                $create = HistoryMemo::create([
                    "id_internal_memo" => $memo->id,
                    "user_id" => auth()->user()->id,
                    "status" => $pic->kategori_proses,
                    "keterangan" => $this->getFlagStatus($pic->kategori_proses) . ' ' . auth()->user()->name,
                    "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                    "waktu" => Carbon::now()->format('h')
                ]);
            }
        }

        if($create){
            return $this->successResponse($create,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getBarangMerk()
    {
        $bMerk = BarangMerk::orderBy('id', 'DESC')->get();

        if($bMerk){
            return $this->successResponse($bMerk,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getBarangTipe($id)
    {
        $bTipe = BarangTipe::where('id_merk', $id)->get();

        if($bTipe){
            return $this->successResponse($bTipe,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getBarangStock(Request $request)
    {
        $cabang = $request->cabang_kode;
        $id_tipe = $request->id_tipe;

        $val = Cabang::where('id', $cabang)->first();
        if($val == !null){
            $bStock = StokBarang::where('id_tipe', $id_tipe)->where('pic', $val->kode)->first();
        }else{
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }

        if($bStock){
            return $this->successResponse($bStock,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function attendanceMaintenance(Request $request, $id)
    {
        $memo = InternalMemoMaintenance::where('link', $id)->first();
        if($memo->kode == $request->kode){
            $memo->update([
                'flag' => 1
            ]);

            if($memo){
                return $this->successResponse($memo,Constants::HTTP_MESSAGE_200, 200);
            } else {
                return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
            }
        }else{
            return $this->errorResponse(Constants::ERROR_MESSAGE_9003, 403);
        }
    }

    public function updateMemoRescheduleV1(Request $request)
    {
        $user = $request->id_user_maintenance;
        $memo = $request->id_memo;
        foreach ($memo as $keys => $memos){
            if(InternalMemoMaintenance::where('id_internal_memo', '=', $memos)->count() > 0){
                InternalMemoMaintenance::where('id_internal_memo', $memos)->delete();
            }
            foreach ($user as $key => $val) {

                $imMaintenance = InternalMemoMaintenance::create([
                    'id_internal_memo' => $memos,
                    'id_user_maintenance' => $val,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'link' => (Str::random(5).$val),
                    'kode' => (Str::random(5)),
                    'flag' => 1,
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

    public function updateMemoRescheduleV2(Request $request, $id)
    {
        $user[] = $request->id_user_maintenance;
        $iMemo[] = $request->id_memo;

        $arr = [];

        foreach ($iMemo[0] as $key => $memos){
            $imMaintenance = InternalMemoMaintenance::where('id_internal_memo', $memos);

            if(!empty($imMaintenance)) {
                foreach ($user[0] as $keys => $users) {

                    $imMaintenance->update([
                        'id_user_maintenance' => $users,
                        'date' => Carbon::now(),
                        'created_by' => auth()->user()->id
                    ]);
                    $arr[] = $imMaintenance->first();
                }
            }
        }

        if($arr){
            return $this->successResponse($arr,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function updateMemoRescheduleV3(Request $request)
    {
        $user[] = $request->id_user_maintenance;
        $iMemo[] = $request->id_memo;
        $barang[] = $request->id_barang;
        $quantity = $request->quantity;
        $cabang = $request->cabang;

        $arr = [];

        foreach ($iMemo[0] as $key => $memos){
            $internalMemo = InternalMemo::where('id', $memos)->get()->pluck('id_cabang');
            $cab = Cabang::where('id', $internalMemo)->get()->pluck('kode');
            $imMaintenance = InternalMemoMaintenance::where('id_internal_memo', $memos);
            $iBarang = InternalMemoBarang::where('id_internal_memo', $memos);

            if(!empty($imMaintenance)) {
                foreach ($user[0] as $keys => $users) {

                    $imMaintenance->update([
                        'id_user_maintenance' => $users,
                        'date' => Carbon::now(),
                        'created_by' => auth()->user()->id
                    ]);
                    $arr[] = $imMaintenance->first();
                }
            }

            if(!empty($iBarang)) {
                foreach ($barang[0] as $i => $barangs) {

                    $iBarang->update([
                        'id_barang' => $barangs,
                        'created_by' => auth()->user()->id
                    ]);
                    $arr[] = $iBarang->first();

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
            }
        }

        if($arr){
            return $this->successResponse($arr,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function webhookTest()
    {
        $device = '089630132793';
        $sender = '081380363569';
        $message = 'uy test';

        function sendFonnte($message)
        {
            $token = env("FONTE_TOKEN");
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => array(
                    'target' => '089630132793',
                    'message' => $message,
                    'url' => 'http://api.pusatgadai.id/api/webhookTest',
                ),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: $token"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }

        if ($message == "test") {
            $reply = "working great!";
        } else {
            $reply = "not working!";
        }

        sendFonnte($reply);
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

    public function getSuratTugas()
    {
        $sTugas = SuratTugas::with('imMaintenance.internalMemo.internalMemoBarang', 'imMaintenance.userMaintenance')->get();

//        $collect = $sTugas->map(function ($query) {
//            $query->imMaintenance->internalMemo;
//
//            return $query;
//        });

        if($sTugas){
            return $this->successResponse($sTugas,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getSuratTugasById($id)
    {
        $sTugas = SuratTugas::where('id', $id)->with('imMaintenance.internalMemo.internalMemoBarang', 'imMaintenance.userMaintenance')->first();

        if($sTugas){
            return $this->successResponse($sTugas,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

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
        } else if($id == 10){
            return "DiHapus";
        } else if($id == 11){
            return "DiTolak";
        }
    }
}
