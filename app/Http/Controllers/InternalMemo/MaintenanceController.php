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
use App\Model\UserMaintenance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MaintenanceController extends Controller
{
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
                    'date' => Carbon::now(),
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

            foreach ($maintenanceUser as $values){
                $user = UserMaintenance::where('id', $values)->first();
                $this->ProceesWaCabang($memo, $cabang, $user);
                $this->ProceesWaMaintenance($memo, $cabang, $user);
            }
        }
    }

    /**
     * Function untuk whatsupp
     */
    public function  ProceesWaCabang($memo, $cabang, $user) {
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
                    No Memo : $memo->im_number
                    Status : PROSES
                    Cabang : $cabang->name
                    Alamat : $cabang->alamat
                    Maintenance : $user->nama
                    No Telp Maintenance : $user->no_telp
                    Tanggal Pekerjaan : $user->created_at,
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

    /**
     * Function untuk whatsupp
     */
    public function  ProceesWaMaintenance($memo, $cabang, $user) {
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
                    No Memo : $memo->im_number
                    Status : PROSES
                    Cabang : $cabang->name
                    Alamat : $cabang->alamat
                    No Telp Cabang : $cabang->telepon
                    Maintenance : $user->nama
                    No Telp Maintenance : $user->no_telp
                    Tanggal Pekerjaan : $user->created_at,
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

    public function cabangByMemoId(Request $request)
    {
        $memo[] = $request->memo_id;

        $cabang = [];
        foreach ($memo[0] as $key => $value){
            $im = InternalMemo::where('id', $value)->first();

            $cabang[] = Cabang::where('id', $im->id_cabang)->first();
        }

        if($cabang){
            return $this->successResponse($cabang,Constants::HTTP_MESSAGE_200, 200);
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
//        $cabang[] = $request->cabang_kode;
//        $id_tipe = $request->id_tipe;
//        foreach ($cabang[0] as $key => $value){
//            $val = Cabang::where('id', $value)->first();
//
//            $bStock[] = StokBarang::where('id_tipe', $id_tipe)->where('pic', $val->kode)->first();
//        }

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
