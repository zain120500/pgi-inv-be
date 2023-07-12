<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangTipe;
use App\Model\BarangMerk;
use App\Helpers\StatusHelper;
use DB;

class BarangTipeController extends Controller
{

    public function index(Request $request)
    {
        // $barang = BarangTipe::query();
      
        // if(!empty($request->tipe)){
        //    $query = $barang->with('barangMerk.barangJeniss')
        //     ->where('tipe', 'like', '%' . $request->tipe . '%')->paginate(15);
           
        //  }
        //  else{
        //    $query = $barang->paginate(15);
        //  }
        $jenis = $request->jenis;
        if (!empty($request->tipe)) {
            $barang = BarangTipe::with('barangMerk.barangJeniss')->where('tipe', 'like', '%' . $request->tipe . '%')->paginate(15);
        } else if (!empty($request->kode_barang)) {
            $barang = BarangTipe::with('barangMerk.barangJeniss')->where('kode_barang', 'like', '%' . $request->kode_barang . '%')->paginate(15);
        } else if(!empty($request->merk)){
            $barang = BarangTipe::with('barangMerk.barangJeniss')->where('id_merk', $request->merk )->paginate(15);
        } else if (!empty($jenis)){
            $barang = BarangTipe::with('barangMerk.barangJeniss')
            ->whereHas('barangMerk',function ($q) use ($jenis){
                $q->where('id_jenis',$jenis);
            })
            ->paginate(15);
        }
        // elseif(!empty($request->merk) && !empty($jenis) ){
        //     echo 'ada merk jenis';
        //     $barang = BarangTipe::with('barangMerk.barangJeniss')->where('id_merk', $request->merk)
        //     ->whereHas('barangMerk',function ($q) use ($jenis){
        //         $q->where('id_jenis',$jenis);
        //     })
        //     ->paginate(15);
        // }
         else {
            $barang = BarangTipe::with('barangMerk.barangJeniss')->paginate(15);
        }

        // $collect = $barang->getCollection()->map(function ($query) use ($request) {
            
        //     // $barangMerk = $query->barangMerk;
        //     // if (!empty($barangMerk)) {
        //     //     $barangMerk->barangJeniss;
        //     // }

        //     return $query;
        // });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            //$barang->setCollection($collect)
            $barang
            //$query
        );
    }

    public function all(Request $request)
    {
        if (!empty($request->tipe)) {
            $barang = BarangTipe::where('tipe', 'like', '%' . $request->tipe . '%')->get();
        } else if (!empty($request->kode_barang)) {
            $barang = BarangTipe::where('kode_barang', 'like', '%' . $request->kode_barang . '%')->get();
        } else if (!empty($request->id_merk)) {
            $barang = BarangTipe::where('id_merk', $request->id_merk)->get();
        } else {
            $barang = BarangTipe::all();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barang
        );
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $tipe = BarangTipe::create([
            "tipe" => $request->tipe,
            "satuan" => $request->satuan,
            "harga" => $request->harga,
            "tipe_kode" => $request->tipe_kode,
            "kode_barang" => $this->getkodebarang($request->id_jenis),
            "id_merk" => $request->id_merk
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $tipe
        );
    }

    public function show($id)
    {
        $query = BarangTipe::find($id);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $query = BarangTipe::where('id', $id)
            ->update([
                "tipe" => $request->tipe,
                "satuan" => $request->satuan,
                "harga" => $request->harga,
                "tipe_kode" => $request->tipe_kode,
                "kode_barang" => $this->getkodebarang($request->id_jenis),
                "id_merk" => $request->id_merk
            ]);


        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }


    public function destroy($id)
    {
        $query = BarangTipe::find($id)->delete();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function getkodebarang($id_jenis) //oke
    {
        $rows = DB::select("SELECT A.id_kategori, B.kode FROM barang_jenis A
          LEFT JOIN kategori B ON B.id = A.id_kategori WHERE A.id ='$id_jenis'");

        $id_kategori = $rows[0]->id_kategori;
        $kode = $rows[0]->kode;

        $query = DB::select("SELECT max(A.kode_barang) as max_code FROM barang_tipe A
                 LEFT JOIN barang_merk B ON B.id = A.id_merk
                 LEFT JOIN barang_jenis C ON C.id = B.id_jenis
                 WHERE C.id_kategori='$id_kategori'");

        $max_id = $query[0]->max_code;
        $max_fix = (int) substr($max_id, 3, 7);

        $max_nik = $max_fix + 1;

        $nik = $kode . sprintf("%07s", $max_nik);
        return $nik;
    }

    public function getBarangTipeAll(Request $request)
    {
        $value = $request->search;

        $record = BarangTipe::where('tipe', 'like', '%' . $value . '%')
            ->orWhere('kode_barang', 'like', '%' . $value . '%')
            ->with('barangMerk.barangJenis')
            ->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }
}
