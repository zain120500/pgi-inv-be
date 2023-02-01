<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Pembelian;
use App\Model\PembelianDetail;
use App\Model\BarangTipe;
use App\Model\Admin;
use App\User;
use DB;

class PembelianController extends Controller
{
    public function index(Request $request)
    {

        if(!empty($request->no_invoice)){
            $query = Pembelian::where('no_invoice', 'like', '%'.$request->no_invoice.'%')->paginate(15);
        } else if(!empty($request->user_input)){
            $query = Pembelian::where('user_input', 'like', '%'.$request->user_input.'%')->paginate(15);
        } else {
            $query = Pembelian::paginate(15);
        }

        $collect = $query->getCollection()->map(function ($q) {
            $q->supplier->makeHidden(['created_at','updated_at']);
            $q['status_flag'] = $this->getCodeFlag($q->flag);
            $q['user_input'] = Admin::select('username')->where('username', $q->user_input)->first();
            return $q;
        });

        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        //
    }

    

    public function store(Request $request)
    {

        $query = Pembelian::create([
            "no_invoice"=> $this->generateInvoice(),
            "tanggal"=> $request->tanggal,
            "id_supplier"=> $request->id_supplier,
            "pic"=> $request->pic,
            "ongkir"=> $request->ongkir,
            "flag" => 0,
            "keterangan"=> $request->keterangan,
            "user_input"=> auth()->user()->admin->username
        ]);

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
            $query = PembelianDetail::create([
                "id_pembelian"=> $request->id_pembelian,
                "id_tipe"=> $request->id_tipe,
                "nomer_barang"=> $barangTipe->kode_barang,
                "harga"=> $request->harga,
                "jumlah"=> $request->jumlah,
                "total_harga"=> (int)$request->harga * (int)$request->jumlah,
                "satuan"=> $request->satuan,
                "imei"=> $request->imei,
                "detail_barang"=> $request->detail_barang,
                "keterangan"=> $request->keterangan,
                "status"=> $request->status
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
        $query = Pembelian::find($id);
        if(!empty($query)){
            $query['status_flag'] = $this->getCodeFlag($query->flag);

            $pembelianDetail = $query->detail;
            foreach ($pembelianDetail as $key => $detail) {
                $detail->tipeBarang->makeHidden(['created_at','updated_at'])
                    ->barangMerk->makeHidden(['created_at','updated_at','id_jenis']);
                
                $detail['status_code'] = $this->getCodeStatus($detail->status);
            }
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
        $query = Pembelian::where('id', $id)
            ->update([
                "no_invoice"=> $request->no_invoice,
                "tanggal"=> $request->tanggal,
                "id_supplier"=> $request->id_supplier,
                "pic"=> $request->pic,
                "ongkir"=> $request->ongkir,
                "flag" => 0,
                "keterangan"=> $request->keterangan,
                "user_input"=> auth()->user()->id
            ]);

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroy($id)
    {
        $query = Pembelian::find($id);
        if(!empty($query)){
            $query->delete();

            PembelianDetail::where('id_pembelian',$query->id)->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroyDetail($id)
    {
        $query = PembelianDetail::find($id);
        if(!empty($query)){
            $query->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }


    public function getCodeFlag($id)
    {
        if($id = 0){
            return "belum proses";
        } else if($id = 1){
            return "sudah diproses";
        } else if($id = 2){
            return "sudah dibayar";
        } else if($id = 3){
            return "diterima";
        } else if($id = 4){
            return "dibatalkan";
        } else if($id = 5){
            return "barang selisih";
        }else if($id = 6){
            return "request void";
        }
    }

    public function getCodeStatus($id)
    {
        if($id = 0){
            return "Pending";
        } else if($id = 1){
            return "Ok";
        } else if($id = 2){
            return "Void";
        } else if($id = 3){
            return "Selisih";
        }else if($id = 4){
            return "request void";
        }
    }

    public function generateInvoice()
    {
        $max_id = DB::statement("select max(no_invoice) as max_code FROM pembelian WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");
        $max_fix = (int) substr($max_id, 9, 4);
        $max_nik = $max_fix + 1;
        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "B".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }
}
