<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Pembelian;
use App\Model\PembelianDetail;
use App\Model\BarangTipe;
use App\Model\BarangMasuk;
use App\Model\Admin;
use App\Model\LogRefund;
use App\User;
use DB;

class PembelianController extends Controller
{
    public function index(Request $request)
    {

        if(!empty($request->no_invoice)){
            $query = Pembelian::where('no_invoice', 'like', '%'.$request->no_invoice.'%')->orderBy('tanggal', 'DESC')->paginate(15);
        } else if(!empty($request->user_input)){
            $query = Pembelian::where('user_input', 'like', '%'.$request->user_input.'%')->orderBy('tanggal', 'DESC')->paginate(15);
        } else if(!empty($request->flag)){
            $query = Pembelian::where('flag', $request->flag)->orderBy('tanggal', 'DESC')->paginate(15);
        } else if(!empty($request->from) and !empty($request->to)){
            $query = Pembelian::whereBetween('tanggal', [$request->from, $request->to])->orderBy('tanggal', 'DESC')->paginate(15);
        } else if(!empty($request->id_supplier)){
            $query = Pembelian::where('id_supplier', $request->id_supplier)->orderBy('tanggal', 'DESC')->paginate(15);
        } else {
            $query = Pembelian::orderBy('tanggal', 'DESC')->paginate(15);
        }

        $collect = $query->getCollection()->map(function ($q) {
            $details = PembelianDetail::where('id_pembelian', $q->id);

            $q['total_unit'] = $details->sum('jumlah');
            $q['total_pembelian'] = $details->sum('total_harga');
            $q->supplier;
            $q['status_flag'] = $this->getCodeFlag($q->flag);
            $q['user_input'] = Admin::select('username')->where('username', $q->user_input)->first();

            return $q;
        });

        return $this->successResponse($query,'Success', 200);
    }

    public function indexDetail(Request $request)
    {
        if(!empty($request->nomer_barang)){
            $query = PembelianDetail::where('nomer_barang', 'like', '%'.$request->nomer_barang.'%')->orderBy('created_at', 'DESC')->paginate(15);
        } else if(!empty($request->id_tipe)){
            $query = PembelianDetail::where('id_tipe', 'like', '%'.$request->id_tipe.'%')->orderBy('created_at', 'DESC')->paginate(15);
        } else if(!empty($request->from) and !empty($request->to)){
            $query = PembelianDetail::whereBetween('created_at', [$request->from, $request->to])->orderBy('created_at', 'DESC')->paginate(15);
        } else if(!empty($request->id_pengiriman)){
            $query = PembelianDetail::where('id_pengiriman', $request->id_pengiriman)->orderBy('created_at', 'DESC')->paginate(15);
        } else if(!empty($request->status)){
            $query = PembelianDetail::where('status', $request->status)->orderBy('created_at', 'DESC')->paginate(15);
        } else {
            $query = PembelianDetail::orderBy('created_at', 'DESC')->paginate(15);
        }

        $collect = $query->getCollection()->map(function ($q) {
            $q['status_flag'] = $this->getCodeFlag($q->flag);
            $q->tipeBarang;
            $q->cabang;
            $q->pembelian->supplier;
            return $q;
        });

        return $this->successResponse($query,'Success', 200);
    }

    public function create()
    {
        return $this->generateInvoice();
    }

    public function store(Request $request)
    {

        $query = Pembelian::create([
            "no_invoice"=> $this->generateInvoice(),
            "tanggal"=> $request->tanggal,
            "id_supplier"=> $request->id_supplier,
            "is_dropship" => $request->is_dropship, // true/false
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
        $query = Pembelian::find($id);
        if(!empty($query)){
            $details = PembelianDetail::where('id_pembelian', $query->id);
            $query['total_unit'] = $details->sum('jumlah');
            $query['total_pembelian'] = $details->sum('total_harga');

            $query['status_flag'] = $this->getCodeFlag($query->flag);

            $pembelianDetail = $query->detail;
            foreach ($pembelianDetail as $key => $detail) {
                $detail->tipeBarang;
                $detail->cabang;
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
                "id_supplier"=> $request->id_supplier,
                "is_dropship" => $request->is_dropship, // true/false
                "pic"=> $request->pic,
                "ongkir"=> $request->ongkir,
                "keterangan"=> $request->keterangan,
                "user_input"=> auth()->user()->admin->username
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
            $query = PembelianDetail::where('id', $id)
                ->update([
                    "id_tipe"=> $request->id_tipe,
                    "nomer_barang"=> $barangTipe->kode_barang,
                    "harga"=> $request->harga,
                    "jumlah"=> $request->jumlah,
                    "total_harga"=> (int)$request->harga * (int)$request->jumlah,
                    "satuan"=> $request->satuan,
                    "imei"=> $request->imei,
                    "detail_barang"=> $request->detail_barang,
                    "keterangan"=> $request->keterangan,
                    "id_gudang"=> ($request->kode_cabang) ? $request->kode_cabang : NULL
                ]);
            if($query){
                return $this->successResponse($query,'Success', 200);
            } else {
                return $this->errorResponse('Data is Null', 403);
            }
        }
    }

    public function updatePembelian(Request $request){ //merubah status pembelian

        $id = $request->id;
        $flag = $request->flag;

        $pembelian = Pembelian::where('id', $id)->first();

        if($flag == 0){  // request void pembelian
            PembelianDetail::where('id_pembelian', $pembelian->id)->update(["status" => 0]); //update barang jadi request void
        } else if($flag == 6){  // request void pembelian
            PembelianDetail::where('id_pembelian', $pembelian->id)->update(["status" => 4]); //update barang jadi request void
        } else if($flag == 2){ //terima barang

        } else if($flag == 3){ //terima barang
            $details = PembelianDetail::where('id_pembelian', $pembelian->id);
            foreach ($details->get() as $key => $detail) {
                $detail->update(["status" => 1]);  //update barang jadi OK

                BarangMasuk::create([
                    "tanggal"=> date("Y-m-d"),
                    "id_tipe"=> $detail->id_tipe,
                    "nomer_barang"=> $detail->nomer_barang,
                    "detail_barang"=> $detail->detail_barang,
                    "imei"=> $detail->imei,
                    "pic"=> $pembelian->pic,
                    "jumlah"=> $detail->jumlah,
                    "satuan"=> $detail->satuan,
                    "total_harga"=> $detail->total_harga,
                    "user_input"=> auth()->user()->admin->username
                ]);

            }
            
        } else if($flag == 4){ // Approve Void Pembelian

            $details = PembelianDetail::where('id_pembelian', $pembelian->id);

            foreach ($details->get() as $key => $detail) {

                $detail->update(["status" => 2]); //update barang jadi void
            
                LogRefund::create([
                    "nomer_barang" => $detail->nomer_barang,
                    "id_tipe" => $detail->id_tipe,
                    "harga" => $detail->harga,
                    "jumlah" => $detail->jumlah,
                    "satuan" => $detail->satuan,
                    "total_harga" => $detail->total_harga,
                    "id_pembelian" => $detail->id_pembelian,
                    "keterangan" => "void pembelian",
                    "status" => 0
                ]);
            }
        }

        $pembelian->update(["flag" => $flag]);

        if($pembelian){
            return $this->successResponse($pembelian,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function updatePembelianDetail(Request $request){ //merubah status pembelian detail (barang pembelian)
        $id = $request->id;
        $status = $request->status;

        $query = PembelianDetail::where('id', $id)->update(["status" => $status]);
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

    public function generateInvoice()
    {
        $max_id = DB::select("select max(no_invoice) as max_code FROM pembelian WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");
        $max_fix = (int) substr($max_id[0]->max_code, 9, 4);
        $max_nik = $max_fix + 1;
        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "B".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }
}
