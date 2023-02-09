<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Model\Admin;
use App\Model\Pengiriman;
use App\Model\PengirimanDetail;
use App\Model\PengirimanKategori;
use App\Model\BarangTipe;
use DB;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::orderBy('tanggal', 'DESC')->paginate(15);
        $collect = $query->getCollection()->map(function ($q) {
            $details = PengirimanDetail::where('id_pengiriman', $q->id);

            $q['total_unit'] = $details->sum('jumlah');
            $q['total_pembelian'] = $details->sum('total_harga');

            $q['kategori'] = PengirimanKategori::where('id', $q->kategori)->first();
            return $q;
        });

        return $this->successResponse($query->setCollection($collect),'Success', 200);

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
            "flag"      => 0,
            "status"    => 0,
            "keterangan"=> $request->keterangan,
            "user_input"=> auth()->user()->admin->username,
            "last_update" => date('Y-m-d H:i:s', strtotime('now'))
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

            $pengiriman_detail = PengirimanDetail::where('id_pengiriman', $query->id)->get();
            $collect = $pengiriman_detail->map(function ($q) {
                $q['barang_tipe'] = BarangTipe::where('id', $q->id_tipe)->get();
                return $q;
            });

            $details = PengirimanDetail::where('id_pengiriman', $query->id);
            $query['total_unit'] = $details->sum('jumlah');
            $query['total_pembelian'] = $details->sum('total_harga');
            
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

    public function destroy($id)
    {
        $query = Pengiriman::find($id);
        if(!empty($query)){
            $query->delete();

            PengirimanDetail::where('id_pengiriman',$query->id)->delete();
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function destroyDetail($id)
    {
        $query = PengirimanDetail::find($id);
        if(!empty($query)){
            $query->delete();
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
}
