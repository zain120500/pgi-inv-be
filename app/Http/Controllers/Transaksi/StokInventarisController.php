<?php

namespace App\Http\Controllers\Transaksi;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\StokInventaris;
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
}
