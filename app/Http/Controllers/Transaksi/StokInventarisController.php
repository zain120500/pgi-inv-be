<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\BarangTipe;
use App\Model\StokBarang;
use App\Model\StokInventaris;
use App\Model\BarangMasuk;
use App\Model\BarangKeluar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StokInventarisController extends Controller
{
    public function all()
    {
        $record = StokInventaris::with('barangTipe.barangMerk.barangJeniss', 'karyawan.jabatan')->orderBy('tanggal', 'DESC')->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function paginate(Request $request)
    {
        $search = $request->search;
        $karyawan = $request->karyawan;
        $startDate = Carbon::parse($request->startDate)->format('Y/m/d');
        $endDate = Carbon::parse($request->endDate)->format('Y/m/d');
        //$record = StokInventaris::with('barangTipe.barangMerk.barangJeniss', 'karyawan.jabatan')->orderBy('tanggal', 'DESC')->paginate(15);
      
        if(!empty($karyawan)){
            $record = StokInventaris::
            // whereHas('barangTipe', function($q)use($search){
            //     $q->where('tipe','like','%'.$search.'%')->orWhere('nomer_barang','like','%'.$search.'%')->orWhere('imei','like','%'.$search.'%');
            // })
            with('karyawan.jabatan')
            ->whereHas('karyawan', function($q)use($karyawan){
                $q->where('nama_karyawan','like','%'.$karyawan.'%')->orWhere('nik','like','%'.$karyawan.'%');
            })
            ->orderBy('id', 'DESC')->paginate(15);
        }
        else{
            $record = StokInventaris::
            whereHas('barangTipe', function($q)use($search){
                $q->where('tipe','like','%'.$search.'%')->orWhere('nomer_barang','like','%'.$search.'%')->orWhere('imei','like','%'.$search.'%');
            })
            ->with('karyawan.jabatan')
            ->orderBy('id', 'DESC')->paginate(15);
        }
        if ($request->startDate && $request->endDate) {
            $record = StokInventaris::
                whereBetween('id', [$startDate, $endDate])
                ->with('karyawan.jabatan')
                ->orderBy('id', 'DESC')->paginate(15);
        }

        $record->map(function($query) use ($search){
            // echo $query['karyawan'];
            $query['id_tipe'] = BarangTipe::where('id',$query->id_tipe)->with('barangMerk.barangJeniss')->first();
        });
        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }
  
    public function create(Request $request)
    {
        $record = StokInventaris::create([
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'nomer_barang' => $request->nomer_barang,
            'id_tipe' => $request->id_tipe,
            'detail_barang' => $request->detail_barang,
            'imei' => $request->imei,
            'gudang' => $request->gudang,
            'pemakai' => $request->pemakai,
            'jumlah_stok' => $request->jumlah_stok,
            'satuan' => $request->satuan,
            'total_asset' => $request->total_asset,
            'flag' => 0,
            'user_input' => auth()->user()->id,
            'last_update' => Carbon::now()
        ]);

        $stokBarang = StokBarang::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where( 'nomer_barang' ,$request->nomer_barang)->first();
        BarangKeluar::create([
            "tanggal" => date("Y-m-d"),
            "id_tipe" => $stokBarang->id_tipe,
            "nomer_barang" => $stokBarang->nomer_barang,
            "detail_barang" => $stokBarang->detail_barang,
            "imei" => $stokBarang->imei,
            "pic" => "0999",
            "jumlah" => $stokBarang->jumlah_stok,
            "satuan" => $stokBarang->satuan,
            "total_harga" => $stokBarang->total_asset,
            "user_input" => "sistem"
        ]);
        $stokBarang->delete();
       
        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function return(Request $request)
    {
       $stokInventaris = StokInventaris::where( 'nomer_barang' ,$request->nomer_barang)->first();
        //return $stokInventaris;
       

        BarangMasuk::create([
            "tanggal" => date("Y-m-d"),
            "id_tipe" => $stokInventaris->id_tipe,
            "nomer_barang" => $stokInventaris->nomer_barang,
            "detail_barang" => $stokInventaris->detail_barang,
            "imei" => $stokInventaris->imei,
            "pic" => "0999",
            "jumlah" => 1,
            "satuan" => $stokInventaris->satuan,
            "total_harga" => $stokInventaris->total_asset,
            "user_input" => "sistem"
        ]);

        // StokBarang::create([
        //     "tanggal" => date("Y-m-d"),
        //     "id_tipe" => $stokInventaris->id_tipe,
        //     "nomer_barang" => $stokInventaris->nomer_barang,
        //     "detail_barang" => $stokInventaris->detail_barang,
        //     "imei" => $stokInventaris->imei,
        //     "pic" => "0999",
        //     "jumlah_stok" => 0,
        //     "satuan" => $stokInventaris->satuan,
        //     "total_asset" => $stokInventaris->total_asset,
        //     "total_harga" => $stokInventaris->total_harga,
        //     "user_input" => "sistem"
        // ]);

       $stokInventaris->delete();
       
        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $stokInventaris
        );
    }
}
