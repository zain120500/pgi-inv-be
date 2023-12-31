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
use App\Model\InternalMemoVendor;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriPicFpp;
use App\Model\Pemakaian;
use App\Model\StokBarang;
use App\Model\UserMaintenance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class MaintenanceController extends Controller
{
    public function newInternalMaintenance(Request $request)
    {
       DB::beginTransaction();

       try {
        $user[] = $request->id_user_maintenance;
        $iMemo[] = $request->id_memo;
        $barangs[] = $request->id_barang;
        $quantity = $request->quantity;
        $pic = $request->pic;
        $vendorType = $request->vendor_type;

        foreach ($iMemo[0] as $key => $memos) {
            if($vendorType == 0) {
            foreach ($user[0] as $keys => $users) {
                    $imMaintenance = InternalMemoMaintenance::create([
                        'id_internal_memo' => $memos,
                        'id_user_maintenance' => $users,
                        'date' => $request->date,
                        'link' => $this->generateRandomString(6),
                        'kode' => $this->generateRandomString(6),
                        'flag' => 0,
                        'created_by' => auth()->user()->id
                    ]);

                    $userMaintenance = UserMaintenance::where('id', $users)->first();
                    $userMaintenance->update(['flag' => 1]); //update pic sedang bertugas

                    InternalMemo::where('id', $memos)->update([
                        'vendor_type' => 0,
                    ]);
                }

                if (count(array_filter($barangs)) > 0) {
                        foreach ($barangs[0] as $i => $barang) {
                            $imBarang = InternalMemoBarang::create([
                                'id_internal_memo' => $memos,
                                'id_maintenance' => $imMaintenance->id,
                                'id_barang' => $barang,
                                'id_user_maintenance' => $users,
                                'date' => $request->date,
                                'link' => $this->generateRandomString(6),
                                'kode' => $this->generateRandomString(6),
                                'flag' => 0,
                                'created_by' => auth()->user()->id
                            ]);

                            BarangHistory::create([
                                'id_barang_tipe' => $barang
                            ]);

                            if ($pic == !null) {
                                if ($quantity == !null) {
                                    $cab1 = Cabang::where('kode', $pic[$i])->get()->pluck('id');
                                    foreach ($cab1 as $cab2) {
                                        InternalMemoBarang::where('id_barang', $barang)->update([
                                            'quantity' => $quantity[$i],
                                            'cabang_id' => $cab2
                                        ]);
                                    }

                                    $cabs = Cabang::where('kode', $pic[$i])->get()->pluck('kode');

                                    foreach ($cabs as $ca) {
                                        $stockBarang = StokBarang::where('id_tipe', $barang)->where('pic', $ca)->first();

                                        Pemakaian::create([
                                            'tanggal' => Carbon::now()->format('Y-m-d'),
                                            'pic' => $stockBarang->pic,
                                            'nomer_barang' => $stockBarang->nomer_barang,
                                            'id_tipe' => $stockBarang->id_tipe,
                                            'jumlah' => $quantity[$i],
                                            'satuan' => $stockBarang->satuan,
                                            'harga' => 0,
                                            'total_harga' => 0,
                                            'imei' => $stockBarang->imei,
                                            'detail_barang' => $stockBarang->detail_barang,
                                            'keperluan' => 'Kebutuhan Cabang',
                                            'pemakai' => 'Cabang',
                                            'user_input' => $stockBarang->user_input,
                                            'last_update' => $stockBarang->last_update
                                        ]);
                                    }
                                } else {
                                    return $this->errorResponse(Constants::ERROR_MESSAGE_9002, 403);
                                }
                            }
                        }
                    }
            }
            if($vendorType == 1) {
                $imMaintenance = InternalMemoVendor::create([
                    'id_internal_memo' => $memos,
                    'vendor_name' => $request->vendor_name,
                    'date' => $request->date,
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);

                InternalMemo::where('id', $memos)->update([
                    'vendor_type' => 1,
                ]);
            }

            $this->whatsuppMessage($memos);
            $this->accMemoByPic($memos);

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
            $imMaintenance
        );
    }

    public function createHistoryBarang($id)
    {

        $barang[] = $id;

        foreach ($barang[0] as $key => $value) {
            $tipe = StokBarang::where('id_tipe', $value)->first();

            BarangHistory::create([
                'id_barang_tipe' => $value
            ]);
        }
    }

    /**
     * Whatsupp Pesan Saat Membuat Tugas Maintenance
     */
    public function whatsuppMessage($memos)
    {

        $test[] = $memos;
        $user = array();
        foreach ($test as $key => $value) {

            $memo = InternalMemo::where('id', $value)->first();
            $kjFpp = KategoriJenisFpp::where('id', $memo->id_kategori_jenis_fpp)->first();
            $cabang = Cabang::where('id', $memo->id_cabang)->first();
            $maintenanceUser = InternalMemoMaintenance::where(['id_internal_memo' =>  $memo->id])->get();

            foreach ($maintenanceUser as $keys => $values) {
                $user = UserMaintenance::where('id', $values->id_user_maintenance)->first();
                $this->ProceesWaCabang($memo, $cabang, $user, $values, $kjFpp);
                $this->ProceesWaMaintenance($memo, $user, $cabang);
            }
        }
    }

    /**
     * Function untuk whatsupp cabang
     */
    public function  ProceesWaCabang($memo, $cabang, $user, $values, $kjFpp)
    {
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
    public function  ProceesWaMaintenance($memo, $user, $cabang)
    {
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
                    No Telp Cabang : $cabang->telepon
                    Maps : https://maps.google.com/?q=$cabang->latitude,$cabang->longitude
                    Info Lebih Lanjut Silahkan Klik Link Dibawah Ini
                    Link Login : http://portal.pusatgadai.id
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

    public function accMemoByPic($id)
    {



        $internalMemo[] = $id;

        $pic = KategoriPicFpp::where('user_id', auth()->user()->id)->first();

        foreach ($internalMemo as $key => $value) {
            $memo = InternalMemo::where('id', $value)->first();

            if ($pic->kategori_proses === 3) {
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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $create
        );
    }

    public function cabangByMemoId(Request $request)
    {
        $memo[] = $request->memo_id;

        $cabang = [];
        foreach ($memo[0] as $key => $value) {
            $im = InternalMemo::where('id', $value)->first();

            $cabang[] = Cabang::where('id', $im->id_cabang)->first();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $cabang
        );
    }

    public function getBarangMerk()
    {
        $bMerk = BarangMerk::orderBy('id', 'DESC')->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $bMerk
        );
    }

    public function getBarangTipe($id)
    {
        $bTipe = BarangTipe::where('id_merk', $id)->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $bTipe
        );
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
        if ($val == !null) {
            $bStock = StokBarang::where('id_tipe', $id_tipe)->where('pic', $val->kode)->first();
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::ERROR_MESSAGE_403,
                null
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $bStock
        );
    }

    public function getPusatStock(Request $request)
    {
        $bStock = StokBarang::where('pic', $request->pic)->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $bStock
        );
    }

    public function attendanceMaintenance(Request $request, $id)
    {

        DB::beginTransaction();

        try {


            $memo = InternalMemoMaintenance::where('link', $id)->first();
            $uM = UserMaintenance::where('id', $memo->id_user_maintenance)->first();

            if ($memo->kode == $request->kode) {
                $memo->update([
                    'flag' => 1
                ]);

                $im = InternalMemo::where('id', $memo->id_internal_memo)->update([
                    'flag' => 12
                ]);

                $hM = HistoryMemo::create([
                    'id_internal_memo' => $memo->id_internal_memo,
                    'user_id' => $uM->user_id,
                    'status' => 12,
                    'keterangan' => 'Maintenance Sudah Hadir',
                    "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                    "waktu" => Carbon::now()->format('h')
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return self::buildResponse(
                Constants::HTTP_CODE_500,
                Constants::ERROR_MESSAGE_500,
                null
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $memo
        );
    }

    public function updateMemoRescheduleV1(Request $request)
    {

        $user = $request->id_user_maintenance;
        $memo = $request->id_memo;
        foreach ($memo as $keys => $memos) {
            if (InternalMemoMaintenance::where('id_internal_memo', '=', $memos)->count() > 0) {
                InternalMemoMaintenance::where('id_internal_memo', $memos)->delete();
            }
            foreach ($user as $key => $val) {

                $imMaintenance = InternalMemoMaintenance::create([
                    'id_internal_memo' => $memos,
                    'id_user_maintenance' => $val,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'link' => (Str::random(5) . $val),
                    'kode' => (Str::random(5)),
                    'flag' => 1,
                    'created_by' => auth()->user()->id
                ]);
            }
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $imMaintenance
        );
    }

    public function updateMemoRescheduleV2(Request $request)
    {




        $user[] = $request->id_user_maintenance;
        $iMemo[] = $request->id_memo;

        $arr = [];

        foreach ($iMemo[0] as $key => $memos) {
            $imMaintenance = InternalMemoMaintenance::where('id_internal_memo', $memos);

            if (!empty($imMaintenance)) {
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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $arr
        );
    }

    public function updateMemoRescheduleV3(Request $request)
    {

        DB::beginTransaction();

        try {

            $user[] = $request->id_user_maintenance;
            $iMemo[] = $request->id_memo;
            $barang[] = $request->id_barang;
            $quantity = $request->quantity;
            $cabang = $request->cabang;

            $arr = [];

            foreach ($iMemo[0] as $key => $memos) {
                $internalMemo = InternalMemo::where('id', $memos)->get()->pluck('id_cabang');
                $cab = Cabang::where('id', $internalMemo)->get()->pluck('kode');
                $imMaintenance = InternalMemoMaintenance::where('id_internal_memo', $memos);
                $iBarang = InternalMemoBarang::where('id_internal_memo', $memos);

                if (!empty($imMaintenance)) {
                    foreach ($user[0] as $keys => $users) {

                        $imMaintenance->update([
                            'id_user_maintenance' => $users,
                            'date' => Carbon::now(),
                            'created_by' => auth()->user()->id
                        ]);
                        $arr[] = $imMaintenance->first();
                    }
                }

                if (!empty($iBarang)) {
                    foreach ($barang[0] as $i => $barangs) {

                        $iBarang->update([
                            'id_barang' => $barangs,
                            'created_by' => auth()->user()->id
                        ]);
                        $arr[] = $iBarang->first();

                        if ($cabang == !null) {
                            if ($quantity == !null) {
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
                            } else {
                                return $this->errorResponse(Constants::ERROR_MESSAGE_9002, 403);
                            }
                        } else {
                            if ($quantity == !null) {
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
                            } else {
                                return $this->errorResponse(Constants::ERROR_MESSAGE_9002, 403);
                            }
                        }
                    }
                }
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
            $arr
        );
    }

    public function updateMemoMaintenance(Request $request)
    {
        //        $iMemo = $request->id_memo;
        //
        //        foreach ($iMemo as $keys => $value){
        //            $memo = InternalMemo::where('id', $value)->first();
        //            $barang = InternalMemoBarang::where('id_internal_memo', $value)->get()->pluck('id_barang');
        //            $cabang = Cabang::where('id', $memo->id_cabang)->first();
        //
        //            $stock = Pemakaian::where(['id_tipe' => [$barang], 'pic' => $cabang->kode])->get();
        //
        //        }
        //        return $stock;

        DB::beginTransaction();

        try {

            $iBarang = $request->id_barang;
            $iMaintenance = $request->id_maintenance;
            $quantity = $request->quantity;
            $cabang = $request->cabang;

            $barang = InternalMemoBarang::where('id_internal_memo', $request->id_memo);
            $maintenance = InternalMemoMaintenance::where('id_internal_memo', $request->id_memo);

            $arr = [];
            $arrs = [];
            if (!empty($barang)) {
                foreach ($iBarang as $keys => $values) {

                    $barang->update([
                        'id_barang' => $values,
                        'quantity' => $quantity[$keys],
                        'cabang_id' => $cabang[$keys]
                    ]);
                    $arr[] = $barang->first();
                }
            }

            if (!empty($maintenance)) {
                foreach ($iMaintenance as $key => $value) {

                    $maintenance->update([
                        'id_user_maintenance' => $value,
                    ]);
                    $arrs[] = $maintenance->first();
                }
            }

            DB::commit();

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $barang
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    /*
     * Anjay
     */
    public function createUserMaitenance(Request $request)
    {
        // DB::beginTransaction();

        // try {

        $userMaintenance = $request->id_user_maintenance;
        $idMemo = $request->id_memo;

        foreach ($idMemo as $key => $memo) {
            foreach ($userMaintenance as $key => $um) {

                $exist = InternalMemoMaintenance::where("id_user_maintenance", $um)->where("id_internal_memo", $memo)->orderBy('id', 'DESC')->first();

                if (empty($exist)) {
                    InternalMemoMaintenance::create([
                        'id_internal_memo' => $memo,
                        'id_user_maintenance' => $um,
                        'date' => $request->date,
                        'link' => $this->generateRandomString(4),
                        'kode' => $this->generateRandomString(4),
                        'flag' => 0,
                        'created_by' => auth()->user()->id
                    ]);
                } else {

                    try {
                        $exist->update([
                            'date' => $request->date,
                            'created_by' => auth()->user()->id
                        ]);
                    } catch (\Throwable $e) {
                        return $e;
                    }
                }
            }


            try {
                $this->whatsuppMessage($memo);
            } catch (\Throwable $e) {
                return self::buildResponse(
                    Constants::HTTP_CODE_500,
                    Constants::ERROR_MESSAGE_500,
                    $e->getMessage()
                );
            }
        }

        // old code

        // foreach ($memo as $key => $value) {
        //     foreach ($user as $keys => $values) {

        //         $update = InternalMemoMaintenance::where('id_internal_memo', $value)->first();

        //         if ($update == null) {
        //             $imMainteance = InternalMemoMaintenance::create([
        //                 'id_internal_memo' => $value,
        //                 'id_user_maintenance' => $values,
        //                 'date' => $request->date,
        //                 'link' => $this->generateRandomString(4),
        //                 'kode' => $this->generateRandomString(4),
        //                 'flag' => 0,
        //                 'created_by' => auth()->user()->id
        //             ]);
        //         } else if ($update->id_user_maintenance !== $values) {
        //             $array[$keys] = $values;
        //         } else if (!empty($update)) {

        //             $updates = InternalMemoMaintenance::where('id_internal_memo', $value);

        //             $updates->update([
        //                 'date' => $request->date,
        //                 'created_by' => auth()->user()->id
        //             ]);

        //             $imMainteance[] = $updates->first();
        //         }
        //     }

        //     // $imMainteance = InternalMemoMaintenance::create([
        //     //     'id_internal_memo' => $value,
        //     //     'id_user_maintenance' => $array,
        //     //     'date' => $request->date,
        //     //     'link' => $this->generateRandomString(6),
        //     //     'kode' => $this->generateRandomString(6),
        //     //     'flag' => 0,
        //     //     'created_by' => auth()->user()->id
        //     // ]);

        //     // $this->whatsuppMessage($value);
        // }

        //     DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return self::buildResponse(
        //         Constants::HTTP_CODE_500,
        //         Constants::ERROR_MESSAGE_500,
        //         $e->getMessage()
        //     );
        // }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            'success'
        );
    }

    public function deleteUserMaintenance(Request $request)
    {


        $memo = $request->id_memo;
        $user = $request->id_user_maintenance;

        if (!empty($memo) && !empty($user)) {
            foreach ($memo as $keys => $values) {
                foreach ($user as $key => $value) {
                    $user = InternalMemoMaintenance::where('id_internal_memo', $values)->where('id_user_maintenance', $value)->get()->pluck('id');
                    $userDel = InternalMemoMaintenance::find($user);
                    $userDel->each->delete();
                }
            }
        } else {
            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::ERROR_MESSAGE_403,
                'error'
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $user
        );
    }

    public function createBarangMaintenance(Request $request)
    {

        DB::beginTransaction();

        try {

            $memo = $request->id_memo;
            $barang = $request->id_barang;
            $quantity = $request->quantity;
            $kode = $request->cabang_kode;

            foreach ($memo as $key => $value) {
                foreach ($barang as $keys => $values) {
                    $cabang = Cabang::where('kode', $kode[$keys])->first();
                    $iBarang = InternalMemoBarang::where('id_barang', $values)->where('id_internal_memo', $value)->first();

                    if (empty($iBarang)) {
                        InternalMemoBarang::create([
                            'id_internal_memo' => $value,
                            'id_barang' => $values,
                            'quantity' => $quantity[$keys],
                            'cabang_id' => $cabang->id,
                            'created_by' => auth()->user()->id
                        ]);
                    } else {
                        $vBarang = ($iBarang->quantity) + $quantity[$keys];
                        $iBarang->update([
                            'id_internal_memo' => $value,
                            'id_barang' => $values,
                            'quantity' => $vBarang,
                            'cabang_id' => $cabang->id,
                            'created_by' => auth()->user()->id
                        ]);
                        $arr[] = $iBarang->first();
                    }

                    $stockBarangs = StokBarang::where('id_tipe', $values)->where('pic', $kode[$keys])->first();

                    $pemakaian = Pemakaian::create([
                        'tanggal' => Carbon::now()->format('Y-m-d'),
                        'pic' => $kode[$keys],
                        'nomer_barang' => $stockBarangs->nomer_barang,
                        'id_tipe' => $stockBarangs->id_tipe,
                        'jumlah' => $quantity[$keys],
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
                }
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
            $pemakaian
        );
    }

    public function deleteBarangMaintenance(Request $request)
    {

        $memo = $request->id_memo;
        $barang = $request->id_barang;
        $cabang = $request->cabang_kode;

        if (!empty($memo) && !empty($barang)) {
            foreach ($memo as $keys => $values) {
                foreach ($barang as $key => $value) {
                    $iBarang = InternalMemoBarang::where('id_internal_memo', $values)->where('id_barang', $value)->get()->pluck('id');
                    $barangDel = InternalMemoBarang::find($iBarang);
                    $barangDel->each->delete();
                }
            }
        } else {
            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::ERROR_MESSAGE_403,
                'error'
            );
        }

        if (!empty($barang) && !empty($cabang)) {
            foreach ($barang as $keys => $values) {
                foreach ($cabang as $key => $value) {
                    $cab = Cabang::where('kode', $value)->get()->pluck('kode');
                    $iBarang = Pemakaian::where('id_tipe', $values)->where('pic', $cab)->get()->pluck('id');
                    $barangDel = Pemakaian::find($iBarang);
                    $barangDel->each->delete();

                    $ibKeluar = BarangKeluar::where('id_tipe', $values)->where('pic', $cab)->get()->pluck('id');
                    $bkDel = BarangKeluar::find($ibKeluar);
                    $bkDel->each->delete();
                }
            }
        } else {
            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::ERROR_MESSAGE_403,
                'error'
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $iBarang
        );
    }

    public function getStockBarangV2(Request $request)
    {
        $value = $request->tipe;
        if (!empty($request->tipe)) {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe')->whereHas('barangTipe', function ($q) use ($value) {
                $q->where('tipe', 'like', '%' . $value . '%');
            })->paginate(10);
        } else {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe')->paginate(10);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stockBarang
        );
    }

    public function getStockBarangV3(Request $request)
    {
        $value = $request->search;
        if (!empty($value)) {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->whereHas('barangTipe', function ($q) use ($value) {
                $q->where('tipe', 'like', '%' . $value . '%')->orWhere('kode_barang', 'like', '%' . $value . '%');
            })->get();
        } else {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->get();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stockBarang
        );
    }

    public function getStockBarangInvent(Request $request)
    {
        $value = $request->search;
        if (!empty($value)) {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->whereHas('barangTipe', function ($q) use ($value) {
                $q->where('tipe_kode' , 1)->where('tipe', 'like', '%' . $value . '%')->orWhere('kode_barang', 'like', '%' . $value . '%');
            })->get();
        } else {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->get();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stockBarang
        );
    }

    public function getStockBarangPemakaian(Request $request)
    {
        $value = $request->search;
        if (!empty($value)) {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->whereHas('barangTipe', function ($q) use ($value) {
                $q->where('tipe_kode' , 0)->where('tipe', 'like', '%' . $value . '%')->orWhere('kode_barang', 'like', '%' . $value . '%');
            })->get();
        } else {
            $stockBarang = StokBarang::where('pic', $request->kode_cabang)->with('barangTipe.barangMerk.barangJenis')->get();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stockBarang
        );
    }

    public function getListMaintenance()
    {
        $listMaintenance = UserMaintenance::withCount('resultJob')->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $listMaintenance
        );
    }

    public function getDetailBarang(Request $request)
    {
        $barang = $request->id_barang;
        $cabang = $request->cabang_id;

        $stock = [];
        foreach ($cabang as $key => $value) {
            $cab = Cabang::where('id', $value)->get()->pluck('kode');
            $stock[] = StokBarang::where('id_tipe', $barang[$key])->where('pic', $cab)->with('barangTipe')->first();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stock
        );
    }

    public function webhookTest()
    {
        header('Content-Type: application/json; charset=utf-8');
        $myObj = new stdClass();
        $myObj->device = "089630132793";
        $myObj->sender = "081380363569";
        $myObj->message = "New York";
        $myObj->text = "New York";
        $myObj->member = "New York";
        $myObj->name = "New York";
        $myObj->location = "New York";
        $myObj->url = "www.pgi.com";
        $myObj->filename = "New York";
        $myObj->extension = "New York";

        $myJSON = json_encode($myObj);
        $data = json_decode($myJSON, true);
        $device = $data['device'];
        $sender = $data['sender'];
        $message = $data['message'];
        $text = $data['text']; //button text
        $member = $data['member']; //group member who send the message
        $name = $data['name'];
        $location = $data['location'];
        $url =  $data['url'];
        $filename =  $data['filename'];
        $extension =  $data['extension'];
        //end

        function sendFonnte($target, $data)
        {
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
                    'target' => $target,
                    'message' => $data['message'],
                ),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: TOKEN"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }

        if ($message == "test") {
            $reply = [
                "message" => "working great!",
            ];
        } elseif ($message == "image") {
            $reply = [
                "message" => "image message",
                "url" => "https://filesamples.com/samples/image/jpg/sample_640%C3%97426.jpg",
            ];
        } elseif ($message == "audio") {
            $reply = [
                "message" => "audio message",
                "url" => "https://filesamples.com/samples/audio/mp3/sample3.mp3",
                "filename" => "music",
            ];
        } elseif ($message == "video") {
            $reply = [
                "message" => "video message",
                "url" => "https://filesamples.com/samples/video/mp4/sample_640x360.mp4",
            ];
        } elseif ($message == "file") {
            $reply = [
                "message" => "file message",
                "url" => "https://filesamples.com/samples/document/docx/sample3.docx",
                "filename" => "document",
            ];
        } else {
            $reply = [
                "message" => "Sorry, i don't understand. Please use one of the following keyword :

        Test
        Audio
        Video
        Image
        File",
            ];
        }

        sendFonnte($sender, $reply);
    }

    function generateRandomString($length = 10)
    {
        $characters = '123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function listMemoByMaintenanceLogin()
    {
        $uMaintenance = UserMaintenance::where('user_id', auth()->user()->id)->first();
        $iMemoMaintenance = InternalMemoMaintenance::where('id_user_maintenance', $uMaintenance->id)->get()->pluck('id_internal_memo');
        $iMemo = InternalMemo::whereIn('id', $iMemoMaintenance)->orderBy('created_at', 'DESC')->withCount('memoMaintenanceCount', 'totalUserMaintenance')->paginate(15);

        $collect = $iMemo->map(function ($query) {
            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query->cabang->kabupatenKota;
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;
            $query->maintenanceUser;
            $query->memoRating;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $iMemo
        );
    }

    public function konfirmasiSelesai(Request $request)
    {
        $uMaintenance = UserMaintenance::where('user_id', auth()->user()->id)->first();
        $iMemoMaintenance = InternalMemoMaintenance::where('id_internal_memo', $request->id_internal_memo)->where('id_user_maintenance', $uMaintenance->id)->first();

        try {

            DB::beginTransaction();

            $iMemoMaintenance->update([
                'flag' => 3
            ]);

            $im = InternalMemo::where('id', $iMemoMaintenance->id_internal_memo)->first();

            $im->update([
                'flag' => 13
            ]);

            $hM = HistoryMemo::create([
                'id_internal_memo' => $iMemoMaintenance->id_internal_memo,
                'user_id' => $uMaintenance->user_id,
                'status' => 13,
                'keterangan' => 'Maintenance Sudah Selesai',
                "tanggal" => Carbon::now()->addDays(1)->format('Y-m-d'),
                "waktu" => Carbon::now()->format('h')
            ]);

            DB::commit();
        } catch (\Exception $e) {
            return self::buildResponse(
                Constants::HTTP_CODE_500,
                Constants::ERROR_MESSAGE_500,
                $e->getMessage()
            );
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            [$iMemoMaintenance, $hM, $im]
        );
    }

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
        } else if ($id == 10) {
            return "DiHapus";
        } else if ($id == 11) {
            return "DiTolak";
        }
    }
}
