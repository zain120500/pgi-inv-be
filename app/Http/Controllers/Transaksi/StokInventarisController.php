<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\StokInventaris;
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

    public function paginate()
    {
        $record = StokInventaris::with('barangTipe.barangMerk.barangJeniss', 'karyawan.jabatan')->orderBy('tanggal', 'DESC')->paginate(15);

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

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }
}
