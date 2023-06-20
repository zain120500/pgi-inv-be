<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\InternalMemo;
use App\User;
use Illuminate\Http\Request;

class LaporanInternalMemo extends Controller
{
    public function laporanPerbaikan(Request $request)
    {
        $internal = InternalMemo::query();

        $internal = $internal->get();

        $internal->map(function ($query) {
//            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = Cabang::where('id', $query->id_cabang)->with('kabupatenKota')->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
    }
}
