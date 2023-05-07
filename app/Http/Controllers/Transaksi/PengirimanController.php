<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\StokBarang;
use App\Model\TblCabang;
use Illuminate\Http\Request;

use App\User;
use App\Model\Admin;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use App\Model\PengirimanKategori;
use App\Model\InternalMemoPengiriman;
use App\Model\BarangTipe;
use App\Model\BarangKeluar;
use App\Model\UserStaffCabang;
use Illuminate\Support\Facades\DB;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        foreach ($this->cabangGlobal() as $cabang){
            if($cabang->kode == "P00001"){
                $query = Pengiriman::orderBy('tanggal', 'DESC')->paginate(15);
            }else{
                $query = Pengiriman::whereIn('pengirim', $this->cabangGlobal()->pluck('kode'))->orderBy('tanggal', 'DESC')->paginate(15);
            }
            $collect = $query->getCollection()->map(function ($q) {
                $details = PengirimanDetail::where('id_pengiriman', $q->id);

                $q['total_unit'] = $details->sum('jumlah');
                $q['total_pembelian'] = $details->sum('total_harga');

                $q['kategori'] = PengirimanKategori::where('id', $q->kategori)->first();
                $q->cabangPengirim;
                $q->cabangPenerima;
                return $q;
            });

            return $this->successResponse($query->setCollection($collect),'Success', 200);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Pengiriman::create([
            "no_invoice"=> $this->getkodeinvoice2(),
            "tanggal"   => $request->tanggal,
            "pengirim"  => $request->pengirim,
            "penerima"  => $request->penerima,
            "kurir"     => $request->kurir,
            "kategori"  => $request->kategori,
            "flag"      => $request->flag,
            "status"    => $request->status,
            "keterangan"=> $request->keterangan,
            "user_input"=> auth()->user()->admin->username,
            "last_update" => date('Y-m-d H:i:s', strtotime('now'))
        ]);

        if(!empty($request->id_internal_memo)) {
            InternalMemoPengiriman::create([
                "id_internal_memo"=> $request->id_internal_memo,
                "id_pengiriman"   => $request->id_pengiriman,
            ]);
        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function storeDetail(Request $request)
    {
        $barangTipe = BarangTipe::find($request->id_tipe);

        if(empty($barangTipe)){
            return $this->errorResponse('Barang Tipe is Null', 403);
        } else {
            $query = PengirimanDetail::create([
                "id_pengiriman"=> $request->id_pengiriman,
                "id_tipe"=> $request->id_tipe,
                "nomer_barang"=> $barangTipe->kode_barang,
                "harga"=> $request->harga,
                "jumlah"=> $request->jumlah,
                "total_harga"=> (int)$request->harga * (int)$request->jumlah,
                "satuan"=> $request->satuan,
                "imei"=> $request->imei,
                "detail_barang"=> $request->detail_barang,
                "keterangan"=> $request->keterangan,
                "id_gudang"=> ($request->kode_cabang) ? $request->kode_cabang : NULL,
                "status"=> 0
            ]);

            if($query){
                return $this->successResponse($query,'Success', 200);
            } else {
                return $this->errorResponse('Data is Null', 403);
            }
        }
    }

    public function show($id)
    {
        $query = Pengiriman::find($id);

        if(!empty($query)){
            if(!empty($query->id_user_input)){
                $query['user_input'] = Admin::where('id',$query->id_user_input)->first()->makeHidden(['password']);
            } else {
                $query['user_input'] = Admin::where('username',$query->user_input)->first()->makeHidden(['password']);
            }
            $query['kategori'] = PengirimanKategori::where('id', $query->kategori)->first();

            $pengiriman_detail = $query->detail;
            $collect = $pengiriman_detail->map(function ($q) {
                $q['status_code'] = $this->getCodeStatus($q->status);
                $q['barang_tipe'] = BarangTipe::where('id', $q->id_tipe)->get();
                return $q;
            });

            $details = PengirimanDetail::where('id_pengiriman', $query->id);
            $query['total_unit'] = $details->sum('jumlah');
            $query['total_pembelian'] = $details->sum('total_harga');
            $query->cabangPengirim;
            $query->cabangPenerima;

            $query['detail'] = $pengiriman_detail;

            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = Pengiriman::where('id', $id)
            ->update([
                "pengirim"  => $request->pengirim,
                "penerima"  => $request->penerima,
                "kurir"     => $request->kurir,
                "kategori"  => $request->kategori,
                "keterangan"=> $request->keterangan
            ]);
        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function updateDetail(Request $request, $id)
    {
        $barangTipe = BarangTipe::find($request->id_tipe);

        if(empty($barangTipe)){
            return $this->errorResponse('Barang Tipe is Null', 403);
        } else {
            $query = PengirimanDetail::where('id', $id)
                ->update([
                    "id_pengiriman"=> $request->id_pengiriman,
                    "id_tipe"=> $request->id_tipe,
                    "nomer_barang"=> $barangTipe->kode_barang,
                    "harga"=> $request->harga,
                    "jumlah"=> $request->jumlah,
                    "total_harga"=> (int)$request->harga * (int)$request->jumlah,
                    "satuan"=> $request->satuan,
                    "imei"=> $request->imei,
                    "detail_barang"=> $request->detail_barang,
                    "keterangan"=> $request->keterangan
                ]);
            if($query){
                return $this->successResponse($query,'Success', 200);
            } else {
                return $this->errorResponse('Data is Null', 403);
            }
        }
    }

    public function updatePengiriman(Request $request){ //merubah status pengiriman
        $id = $request->id;
        $flag = $request->flag;
        $status = $request->status;

        $pengiriman = Pengiriman::where('id', $id)->first();

        if($flag == 1){  // Proses Pengiriman
            $pengiriman->update(["status" => 2, "flag"=>1]);

        } else if($status == 1){  // Terima barang pengiriman
            $pengiriman->update(["status" => 1, "flag"=>0]);

        } else if($status == 2){ //pengiriman dikirim
            $pengiriman->update(["status" => 2]);
        } else if($status == 3){ //pengiriman dikirim
            $pengiriman->update(["status" => 3]);

            foreach ($pengiriman->detail as $key => $detail) {

                BarangKeluar::create([
                    "tanggal" => date("Y-m-d"),
                    "id_tipe" => $detail->id_tipe,
                    "nomer_barang" => $detail->nomer_barang,
                    "detail_barang" => $detail->detail_barang,
                    "pic" => $pengiriman->pengirim,
                    "jumlah" => $detail->jumlah,
                    "satuan" => $detail->satuan,
                    "total_harga" => $detail->total_harga,
                    "imei" => $detail->imei,
                    "user_input" => auth()->user()->admin->username,
                ]);

                $detail->update(['status'=>1]);
            }

            if($pengiriman){
                return $this->successResponse($pengiriman,'Success', 200);
            } else {
                return $this->errorResponse('Data is Null', 403);
            }
        }
    }


    public function updatePengirimanDetail(Request $request){ //merubah status pengiriman
        $id = $request->id;
        $status = $request->status;

        $query = PengirimanDetail::where('id', $id)->first();
        $pengiriman = Pengiriman::where('id', $query->id_pengiriman)->first();

        $query->update(["status" => $status]);

        if($status == 1) { //jika barang pengiriman diterima
            $bStockPengirim = StokBarang::where([
                'pic' => $pengiriman->pengirim,
                'nomer_barang' => $query->nomer_barang
            ])->first();

            $bStockPenerima = StokBarang::where([
                'pic' => $pengiriman->penerima,
                'nomer_barang' => $query->nomer_barang
            ])->first();

            $pengurangan = ($bStockPengirim->jumlah_stok-$request->jumlah);
            $penambahan = ($bStockPenerima->jumlah_stok+$request->jumlah);

            try {
                StokBarang::where('id', $bStockPengirim->id)->update([
                    'nomer_barang' => $bStockPengirim->nomer_barang,
                    'id_tipe' => $bStockPengirim->id_tipe,
                    'detail_barang' => $bStockPengirim->detail_barang,
                    'imei' => $bStockPengirim->imei,
                    'pic' => $bStockPengirim->pic,
                    'satuan' => $bStockPengirim->satuan,
                    'total_asset' => $bStockPengirim->total_asset,
                    'user_input' => $bStockPengirim->user_input,
                    'last_update' => $bStockPengirim->last_update,
                    'jumlah_stok' => $pengurangan
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            try {
                StokBarang::where('id', $bStockPenerima->id)->update([
                    'nomer_barang' => $bStockPenerima->nomer_barang,
                    'id_tipe' => $bStockPenerima->id_tipe,
                    'detail_barang' => $bStockPenerima->detail_barang,
                    'imei' => $bStockPenerima->imei,
                    'pic' => $bStockPenerima->pic,
                    'satuan' => $bStockPenerima->satuan,
                    'total_asset' => $bStockPenerima->total_asset,
                    'user_input' => $bStockPenerima->user_input,
                    'last_update' => $bStockPenerima->last_update,
                    'jumlah_stok' => $penambahan
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            try {
                BarangKeluar::create([
                    "tanggal" => date("Y-m-d"),
                    "id_tipe" => $query->id_tipe,
                    "nomer_barang" => $query->nomer_barang,
                    "detail_barang" => $query->detail_barang,
                    "pic" => $pengiriman->pengirim,
                    "jumlah" => $query->jumlah,
                    "satuan" => $query->satuan,
                    "total_harga" => $query->total_harga,
                    "imei" => $query->imei,
                    "user_input" => auth()->user()->admin->username,
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroy($id)
    {
        $query = Pengiriman::find($id);
        $pengiriman = PengirimanDetail::where('id_pengiriman', $query->id)->first();

        if(!empty($query && !empty($pengiriman))) {
            DB::beginTransaction();
            try {
                BarangKeluar::where('pic', $query->pengirim)->where('id_tipe', $pengiriman->id_tipe)->delete();
                $query->delete();

                PengirimanDetail::where('id_pengiriman', $query->id)->delete();
//                InternalMemoPengiriman::where("id_pengiriman", $query->id)->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        }else if(empty($pengiriman)){
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        }

        if(!empty($query)){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroyDetail($id)
    {
        $query = PengirimanDetail::find($id);
        $pengiriman = Pengiriman::where('id', $query->id_pengiriman)->first();

        if(!empty($query)){

            DB::beginTransaction();
            try {
                BarangKeluar::where('id_tipe', $query->id_tipe)->where('pic', $pengiriman->pengirim)->delete();
                $query->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }

            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function getkodeinvoice2()
    {
        $max_id = DB::select("select max(no_invoice) as max_code FROM pengiriman WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");

        $max_fix = (int) substr($max_id[0]->max_code, 9, 4);
        $max_nik = $max_fix + 1;

        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "K".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }

    public function getCabang(Request $request)
    {
        $userCabang = Cabang::where('kepala_unit_id', auth()->user()->id)
                ->orWhere('kepala_cabang_id', auth()->user()->id)
                ->orWhere('kepala_cabang_senior_id', auth()->user()->id)
                ->orWhere('area_manager_id', auth()->user()->id)
                ->first();

        if($userCabang->kepala_cabang_id == !null){
            $cabang = Cabang::where('kepala_cabang_id', $userCabang->kepala_cabang_id)->get();
        } else if ($userCabang->kepala_cabang_senior_id == !null){
            $cabang = Cabang::where('kepala_cabang_senior_id', $userCabang->kepala_cabang_senior_id)->get();
        } else if ($userCabang->area_manager_id == !null){
            $cabang = Cabang::where('area_manager_id', $userCabang->area_manager_id)->get();
        }

        if($cabang){
            return $this->successResponse($cabang,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getCabangPusat(Request $request)
    {
        $cabang = Cabang::whereLike('name', $request->name)->get();

        if($cabang){
            return $this->successResponse($cabang,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getCodeFlag($id)
    {
        if($id == 0){
            return "belum proses";
        } else if($id == 1){
            return "sudah diproses";
        } else if($id == 2){
            return "sudah dibayar";
        } else if($id == 3){
            return "diterima";
        } else if($id == 4){
            return "dibatalkan";
        } else if($id == 5){
            return "barang selisih";
        }else if($id == 6){
            return "request void";
        }
    }

    public function getCodeStatus($id)
    {
        if($id == 0){
            return "Pending";
        } else if($id == 1){
            return "Ok";
        } else if($id == 2){
            return "Void";
        } else if($id == 3){
            return "Selisih";
        }else if($id == 4){
            return "request void";
        }
    }
}
