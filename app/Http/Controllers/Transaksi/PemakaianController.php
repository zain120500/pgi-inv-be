<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Pemakaian;
use Illuminate\Http\Request;
use App\Model\BarangTipe;
use App\Model\BarangKeluar;
use App\Model\StokBarang;
use Faker\Core\Number;
use Illuminate\Support\Carbon;

class PemakaianController extends Controller
{
    public function all()
    {
        $pemakaian = Pemakaian::where('pic', $this->cabangGlobal()->pluck('kode'))->get();
       
        $pemakaian->map(function ($query) {
            $query->cabang;
            $query->barangTipe->barangMerk->barangJeniss;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $pemakaian
        );
    }

    public function paginate(Request $request)
    {
        $search = $request->search;

        $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
        $endDate = Carbon::parse($request->endDate)->format('Y/m/d');

        if(empty($request->startDate) && empty($request->endDate)){
            $pemakaian = Pemakaian::orderBy('id','DESC')->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('nomer_barang', 'like', '%' . $search . '%');
            })->paginate(15);
        }
        if($request->startDate && $request->endDate){
            $pemakaian = Pemakaian::orderBy('id','DESC')->whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('nomer_barang', 'like', '%' . $search . '%');
            })
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->paginate(15);
        }
        $pemakaian->map(function ($query) use ($search) {
           
            $query->cabang;
            $query['barang_tipe'] = BarangTipe::where('id', $query->id_tipe)->with('barangMerk.barangJeniss')->first();

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $pemakaian
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        
        $stokBarang = StokBarang::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where('nomer_barang',$request->nomer_barang)->first();
        $hargaSatuan = $stokBarang['total_asset'] / $stokBarang['jumlah_stok'];
        $hargaKeluar = $hargaSatuan * intval($request->jumlah);
        //echo $stokBarang;
        
        $pemakaian = Pemakaian::create([
            'tanggal' => $request->tanggal,
            'pic' => $request->pic,
            'nomer_barang' => $request->nomer_barang,
            'id_tipe' => $request->id_tipe,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'harga' => (int)$hargaSatuan,
            'total_harga' => (int)$hargaKeluar,
            'imei' => $request->imei,
            'detail_barang' => $request->detail_barang,
            'keperluan' => $request->keperluan,
            'pemakai' => $request->pemakai,
            'user_input' => $request->user_input,
            'last_update' => $request->last_update
        ]);

        // BarangKeluar::create([
        //     "tanggal" => date("Y-m-d"),
        //     "id_tipe" => $request->id_tipe,
        //     "nomer_barang" => $request->nomer_barang,
        //     "detail_barang" => $request->detail_barang,
        //     "imei" => $request->imei,
        //     "pic" => "0999",
        //     "jumlah" => $request->jumlah,
        //     "satuan" => $request->satuan,
        //     "total_harga" => $request->total_harga,
        //     "user_input" => "sistem"
        // ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $pemakaian
        );
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }
    public function delete($id)
    {
        try {
            $barangDel = Pemakaian::find($id);
            echo $barangDel;
            // $jumlah = StokBarang::where('nomer_barang', $barangDel->nomer_barang)->where('pic' , '0999')->first()->jumlah_stok;
            // $jumlah + $barangDel->jumlah;
            //  StokBarang::where('nomer_barang', $barangDel->nomer_barang)->where('pic' , '0999')
            // ->update([
            //         "jumlah_stok" => $jumlah + $barangDel->jumlah,
            //     ]);
            // // return $stokBarang;
            // // die;
            $barangKeluar = BarangKeluar::where('pic' , '0999')->where('nomer_barang',$barangDel->nomer_barang)->where('tanggal',$barangDel->tanggal)->orderBy('last_update','desc')->first();
            $barangDel->delete();
            BarangKeluar::find($barangKeluar->id)->delete();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barangDel
        ); 
    }
    public function sync(){
        $pemakaian = Pemakaian::where('pic','0999')->where('total_harga' ,'=' ,0)->get();
        foreach($pemakaian as $p){
            //echo $p['nomer_barang'];
            $totalAset = StokBarang::where('pic','0999')->where('nomer_barang',$p['nomer_barang'])->first();
            //echo $totalAset['total_asset'];
            echo $p;
           // echo $totalAset ;
            if($totalAset != null){
                $hargaSatuan = $totalAset['total_asset'] / $p['jumlah'];
                $hargaKeluar = $hargaSatuan * intval($p['jumlah']);
                Pemakaian::find($p['id'])->update([
                    'harga' => $hargaSatuan,
                    'total_harga' => $hargaKeluar
                ]);
            }
           
        }
   
        return $pemakaian;
    }
}
