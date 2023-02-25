<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangTipe;
use App\Model\HistoryMemo;
use App\Model\InternalMemo;
use App\Model\InternalMemoBarang;
use App\Model\InternalMemoMaintenance;
use App\Model\KategoriPicFpp;
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
        $barang[] = $request->id_barang;

        foreach ($iMemo[0] as $key => $memos){
            foreach ($user[0] as $keys => $users){
                $imMaintenance = InternalMemoMaintenance::create([
                    'id_internal_memo' => $memos,
                    'id_user_maintenance' => $users,
                    'date' => Carbon::now(),
                    'created_by' => auth()->user()->id
                ]);
            }

            foreach ($barang[0] as $ke => $barangs){
                $imBarang = InternalMemoBarang::create([
                    'id_internal_memo' => $memos,
                    'id_maintenance' => $imMaintenance->id,
                    'id_barang' => $barangs,
                    'created_by' => auth()->user()->id
                ]);
            }
            $this->whatsuppMessage($memos);
            $this->accMemoByPic($memos);
        }

        if($imBarang){
            return $this->successResponse($imBarang,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    /**
     * Whatsupp Pesan Saat Membuat Tugas Maintenance
     */
    public function whatsuppMessage($id)
    {
        $test[] = $id;
        foreach ($test as $key => $value){
            $memo = InternalMemo::where('id', $value)->first();
            $token = "XHFEifo#nyT3A7UZf6c8";
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
                    'target' => '089630132793',
                    'message' => "
                        No Memo : $memo->im_number
                        Status : PROSES
                        Pekerja : Richard dan Aldi
                        Nik : 123325346
                        Tanggal Pekerjaan : 20-02-2015
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
}
