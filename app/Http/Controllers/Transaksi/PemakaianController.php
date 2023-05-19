<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Pemakaian;
use Illuminate\Http\Request;
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
        $pemakaian = Pemakaian::whereIn('pic', $this->cabangGlobal()->pluck('kode'))
            ->whereHas('barangTipe', function ($q) use ($search) {
                $q->where('tipe', 'like', '%' . $search . '%')->orWhere('kode_barang', 'like', '%' . $search . '%');
            })->paginate(15);

        $pemakaian->map(function ($query) use ($search) {
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $pemakaian = Pemakaian::create([
            'tanggal' => $request->tanggal,
            'pic' => $request->pic,
            'nomer_barang' => $request->nomer_barang,
            'id_tipe' => $request->id_tipe,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'harga' => $request->harga,
            'total_harga' => $request->total_harga,
            'imei' => $request->imei,
            'detail_barang' => $request->detail_barang,
            'keperluan' => $request->keperluan,
            'pemakai' => $request->pemakai,
            'user_input' => $request->user_input,
            'last_update' => $request->last_update
        ]);

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

    public function destroy($id)
    {
        //
    }
}
