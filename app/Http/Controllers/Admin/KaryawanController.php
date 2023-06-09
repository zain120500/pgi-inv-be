<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\TblKaryawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function all()
    {
        $karyawan = TblKaryawan::get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $karyawan
        );
    }

    public function getAllByDivisiId(Request $request)
    {
        $karyawan = TblKaryawan::where('id_divisi', $request->id_divisi)->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $karyawan
        );
    }
}
